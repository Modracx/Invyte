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

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $perPage = min((int) $request->input('per_page', 20), 100);
        $search = $request->input('search', '');
        $lowStock = $request->boolean('low_stock');
        $threshold = (float) env('IMS_LOW_STOCK_THRESHOLD', 10);

        $query = Product::withName()->withPrice()
            ->with('stockItem');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('catalog_product_entity.sku', 'like', "%{$search}%")
                  ->orWhere('name_attr.value', 'like', "%{$search}%");
            });
        }

        // Filter by assigned sources for managers
        if (!$user->isAdmin()) {
            $sourceCodes = $user->getAssignedSourceCodes();
            $skus = InventorySourceItem::whereIn('source_code', $sourceCodes)
                ->pluck('sku')->unique()->toArray();
            $query->whereIn('catalog_product_entity.sku', $skus);
        }

        // Low stock filter
        if ($lowStock) {
            $lowSkus = InventorySourceItem::select('sku')
                ->where('quantity', '<=', $threshold)
                ->where('quantity', '>', 0)
                ->pluck('sku')->toArray();
            $query->whereIn('catalog_product_entity.sku', $lowSkus);
        }

        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $product = Product::withName()->withPrice()
            ->where('catalog_product_entity.entity_id', $id)
            ->firstOrFail();

        $sourceItemsQuery = InventorySourceItem::where('sku', $product->sku);

        if (!$user->isAdmin()) {
            $sourceItemsQuery->whereIn('source_code', $user->getAssignedSourceCodes());
        }

        $sourceItems = $sourceItemsQuery->get();
        $stockItem = CatalogStockItem::where('product_id', $id)->first();

        return response()->json([
            'product' => $product,
            'stock_item' => $stockItem,
            'source_items' => $sourceItems,
        ]);
    }

    public function updateStock(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $this->validate($request, [
            'source_code' => 'required|string',
            'quantity'    => 'required|numeric|min:0',
            'reason'      => 'nullable|string|max:255',
            'location_id' => 'nullable|integer|exists:ims_source_locations,id',
        ]);

        $sourceCode = $request->input('source_code');

        if (!$user->canAccessSource($sourceCode)) {
            return response()->json(['message' => 'Access denied to this source'], 403);
        }

        $product    = Product::where('entity_id', $id)->firstOrFail();
        $newQty     = (float) $request->input('quantity');
        $locationId = $request->input('location_id') ? (int) $request->input('location_id') : null;

        DB::transaction(function () use ($product, $sourceCode, $newQty, $locationId, $request, $user) {
            // Get current qty
            $sourceItem = InventorySourceItem::where('source_code', $sourceCode)
                ->where('sku', $product->sku)
                ->first();

            $oldQty = $sourceItem ? (float) $sourceItem->quantity : 0;
            $delta  = $newQty - $oldQty;

            // Update or create MSI source item
            InventorySourceItem::updateOrCreate(
                ['source_code' => $sourceCode, 'sku' => $product->sku],
                ['quantity' => $newQty, 'status' => $newQty > 0 ? 1 : 0]
            );

            // If a specific location was selected, adjust its qty by the same delta
            if ($locationId !== null) {
                \App\Models\SourceItemLocation::where('source_code', $sourceCode)
                    ->where('sku', $product->sku)
                    ->where('location_id', $locationId)
                    ->update(['qty' => \Illuminate\Support\Facades\DB::raw("GREATEST(0, qty + {$delta})")]);
            }

            // Update legacy cataloginventory (total across all sources for default stock)
            $totalQty = InventorySourceItem::where('sku', $product->sku)->sum('quantity');
            CatalogStockItem::where('product_id', $product->entity_id)->update([
                'qty'         => $totalQty,
                'is_in_stock' => $totalQty > 0 ? 1 : 0,
            ]);

            // Log movement
            StockMovement::create([
                'source_code' => $sourceCode,
                'sku'         => $product->sku,
                'type'        => 'adjustment',
                'qty_before'  => $oldQty,
                'qty_after'   => $newQty,
                'qty_change'  => $delta,
                'reason'      => $request->input('reason', 'Manual adjustment'),
                'user_id'     => $user->id,
            ]);
        });

        return response()->json(['message' => 'Stock updated successfully']);
    }
}
