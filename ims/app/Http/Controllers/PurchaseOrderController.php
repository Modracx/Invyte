<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Http\Controllers;

use App\Models\CatalogStockItem;
use App\Models\InventorySourceItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $perPage = min((int) $request->input('per_page', 20), 100);

        $query = PurchaseOrder::with(['supplier:supplier_id,name', 'createdBy:id,name'])
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin()) {
            $query->whereIn('source_code', $user->getAssignedSourceCodes());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $this->validate($request, [
            'supplier_id' => 'required|integer',
            'source_code' => 'required|string',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sku' => 'required|string',
            'items.*.qty_ordered' => 'required|numeric|min:1',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
        ]);

        $sourceCode = $request->input('source_code');
        if (!$user->canAccessSource($sourceCode)) {
            return response()->json(['message' => 'Access denied to this source'], 403);
        }

        $po = DB::transaction(function () use ($request, $user) {
            $po = PurchaseOrder::create([
                'supplier_id' => $request->input('supplier_id'),
                'source_code' => $request->input('source_code'),
                'status' => 'draft',
                'expected_date' => $request->input('expected_date'),
                'notes' => $request->input('notes'),
                'created_by' => $user->id,
            ]);

            foreach ($request->input('items') as $item) {
                $product = Product::withName()->where('catalog_product_entity.sku', $item['sku'])->first();
                PurchaseOrderItem::create([
                    'po_id' => $po->id,
                    'sku' => $item['sku'],
                    'product_name' => $product ? $product->name : $item['sku'],
                    'qty_ordered' => $item['qty_ordered'],
                    'qty_received' => 0,
                    'unit_cost' => $item['unit_cost'] ?? 0,
                ]);
            }

            return $po->load(['items', 'supplier:supplier_id,name']);
        });

        return response()->json($po, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $po = PurchaseOrder::with(['items', 'supplier', 'createdBy:id,name'])->findOrFail($id);

        if (!$user->canAccessSource($po->source_code)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json($po);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $po = PurchaseOrder::findOrFail($id);

        if (!$user->canAccessSource($po->source_code)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if (!in_array($po->status, ['draft', 'pending'])) {
            return response()->json(['message' => 'Cannot edit a received PO'], 422);
        }

        $po->update($request->only(['notes', 'expected_date', 'status']));
        return response()->json($po);
    }

    public function receive(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $po = PurchaseOrder::with('items')->findOrFail($id);

        if (!$user->canAccessSource($po->source_code)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if ($po->status === 'received') {
            return response()->json(['message' => 'PO already fully received'], 422);
        }

        $this->validate($request, [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.qty_received' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($po, $request, $user) {
            foreach ($request->input('items') as $receivedItem) {
                $poItem = $po->items->firstWhere('id', $receivedItem['id']);
                if (!$poItem) continue;

                $qtyReceiving = (float) $receivedItem['qty_received'];
                if ($qtyReceiving <= 0) continue;

                $product = Product::where('sku', $poItem->sku)->first();
                if (!$product) continue;

                // Update source item
                $sourceItem = InventorySourceItem::where('source_code', $po->source_code)
                    ->where('sku', $poItem->sku)->first();
                $oldQty = $sourceItem ? $sourceItem->quantity : 0;
                $newQty = $oldQty + $qtyReceiving;

                InventorySourceItem::updateOrCreate(
                    ['source_code' => $po->source_code, 'sku' => $poItem->sku],
                    ['quantity' => $newQty, 'status' => 1]
                );

                // Update legacy stock
                $totalQty = InventorySourceItem::where('sku', $poItem->sku)->sum('quantity');
                CatalogStockItem::where('product_id', $product->entity_id)->update([
                    'qty' => $totalQty,
                    'is_in_stock' => 1,
                ]);

                // Log movement
                StockMovement::create([
                    'source_code' => $po->source_code,
                    'sku' => $poItem->sku,
                    'type' => 'receive',
                    'qty_before' => $oldQty,
                    'qty_after' => $newQty,
                    'qty_change' => $qtyReceiving,
                    'reason' => "Received via PO #{$po->id}",
                    'user_id' => $user->id,
                    'reference_id' => $po->id,
                ]);

                $poItem->update(['qty_received' => $poItem->qty_received + $qtyReceiving]);
            }

            // Determine PO status
            $po->refresh();
            $allReceived = $po->items->every(fn($i) => $i->qty_received >= $i->qty_ordered);
            $anyReceived = $po->items->some(fn($i) => $i->qty_received > 0);

            $po->update(['status' => $allReceived ? 'received' : ($anyReceived ? 'partial' : 'pending')]);
        });

        return response()->json(['message' => 'Stock received successfully']);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $po = PurchaseOrder::findOrFail($id);

        if (!$user->isAdmin() && $po->status !== 'draft') {
            return response()->json(['message' => 'Managers can only delete draft POs'], 403);
        }

        if (!$user->canAccessSource($po->source_code)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $po->items()->delete();
        $po->delete();

        return response()->json(['message' => 'Purchase order deleted']);
    }
}
