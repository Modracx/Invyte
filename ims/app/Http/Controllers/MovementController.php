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
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $perPage = min((int) $request->input('per_page', 20), 100);

        $query = StockMovement::with('user:id,name')
            ->orderBy('created_at', 'desc');

        if (!$user->isAdmin()) {
            $query->whereIn('source_code', $user->getAssignedSourceCodes());
        }

        if ($request->filled('sku')) {
            $query->where('sku', 'like', '%' . $request->input('sku') . '%');
        }
        if ($request->filled('source_code')) {
            $query->where('source_code', $request->input('source_code'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $this->validate($request, [
            'source_code' => 'required|string',
            'sku' => 'required|string',
            'qty_change' => 'required|numeric',
            'reason' => 'nullable|string|max:255',
        ]);

        $sourceCode = $request->input('source_code');

        if (!$user->canAccessSource($sourceCode)) {
            return response()->json(['message' => 'Access denied to this source'], 403);
        }

        $product = Product::where('sku', $request->input('sku'))->firstOrFail();
        $qtyChange = (float) $request->input('qty_change');

        DB::transaction(function () use ($product, $sourceCode, $qtyChange, $request, $user) {
            $sourceItem = InventorySourceItem::where('source_code', $sourceCode)
                ->where('sku', $product->sku)
                ->first();

            $oldQty = $sourceItem ? $sourceItem->quantity : 0;
            $newQty = max(0, $oldQty + $qtyChange);

            InventorySourceItem::updateOrCreate(
                ['source_code' => $sourceCode, 'sku' => $product->sku],
                ['quantity' => $newQty, 'status' => $newQty > 0 ? 1 : 0]
            );

            $totalQty = InventorySourceItem::where('sku', $product->sku)->sum('quantity');
            CatalogStockItem::where('product_id', $product->entity_id)->update([
                'qty' => $totalQty,
                'is_in_stock' => $totalQty > 0 ? 1 : 0,
            ]);

            StockMovement::create([
                'source_code' => $sourceCode,
                'sku' => $product->sku,
                'type' => 'adjustment',
                'qty_before' => $oldQty,
                'qty_after' => $newQty,
                'qty_change' => $qtyChange,
                'reason' => $request->input('reason', 'Manual adjustment'),
                'user_id' => $user->id,
            ]);
        });

        return response()->json(['message' => 'Movement recorded'], 201);
    }
}
