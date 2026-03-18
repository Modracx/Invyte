<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Http\Controllers;

use App\Models\ImsAlert;
use App\Models\InventorySource;
use App\Models\InventorySourceItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $threshold = (float) env('IMS_LOW_STOCK_THRESHOLD', 10);

        $sourceFilter = $user->isAdmin() ? null : $user->getAssignedSourceCodes();

        $productCount = Product::count();

        $lowStockQuery = InventorySourceItem::where('quantity', '<=', $threshold)->where('quantity', '>', 0);
        if ($sourceFilter) $lowStockQuery->whereIn('source_code', $sourceFilter);
        $lowStockCount = $lowStockQuery->distinct('sku')->count();

        $sourceCount = $user->isAdmin()
            ? InventorySource::where('enabled', 1)->count()
            : count($user->getAssignedSourceCodes());

        $poQuery = PurchaseOrder::whereIn('status', ['draft', 'pending', 'partial']);
        if ($sourceFilter) $poQuery->whereIn('source_code', $sourceFilter);
        $pendingPoCount = $poQuery->count();

        $recentMovementsQuery = StockMovement::with('user:id,name')->orderBy('created_at', 'desc')->limit(10);
        if ($sourceFilter) $recentMovementsQuery->whereIn('source_code', $sourceFilter);
        $recentMovements = $recentMovementsQuery->get();

        return response()->json([
            'total_products' => $productCount,
            'low_stock_count' => $lowStockCount,
            'total_sources' => $sourceCount,
            'pending_po_count' => $pendingPoCount,
            'recent_movements' => $recentMovements,
        ]);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $threshold = (float) $request->input('threshold', env('IMS_LOW_STOCK_THRESHOLD', 10));

        $query = InventorySourceItem::where('quantity', '<=', $threshold)
            ->where('quantity', '>=', 0)
            ->orderBy('quantity', 'asc');

        if (!$user->isAdmin()) {
            $query->whereIn('source_code', $user->getAssignedSourceCodes());
        }

        $items = $query->get();

        // Attach product names
        $skus = $items->pluck('sku')->unique()->toArray();
        $names = DB::table('catalog_product_entity')
            ->join('catalog_product_entity_varchar', function ($j) {
                $j->on('catalog_product_entity.entity_id', '=', 'catalog_product_entity_varchar.entity_id')
                  ->where('catalog_product_entity_varchar.attribute_id', (int)env('MAGENTO_ATTR_NAME', 73))
                  ->where('catalog_product_entity_varchar.store_id', 0);
            })
            ->whereIn('catalog_product_entity.sku', $skus)
            ->pluck('catalog_product_entity_varchar.value', 'catalog_product_entity.sku');

        $result = $items->map(fn($i) => array_merge($i->toArray(), [
            'product_name' => $names[$i->sku] ?? $i->sku,
        ]));

        return response()->json($result);
    }

    public function stockValue(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $query = DB::table('inventory_source_item as isi')
            ->join('catalog_product_entity as cpe', 'isi.sku', '=', 'cpe.sku')
            ->leftJoin('catalog_product_entity_decimal as cost_attr', function ($j) {
                $j->on('cpe.entity_id', '=', 'cost_attr.entity_id')
                  ->where('cost_attr.attribute_id', (int)env('MAGENTO_ATTR_COST', 81))
                  ->where('cost_attr.store_id', 0);
            })
            ->leftJoin('catalog_product_entity_decimal as price_attr', function ($j) {
                $j->on('cpe.entity_id', '=', 'price_attr.entity_id')
                  ->where('price_attr.attribute_id', (int)env('MAGENTO_ATTR_PRICE', 77))
                  ->where('price_attr.store_id', 0);
            })
            ->select(
                'isi.source_code',
                DB::raw('SUM(isi.quantity) as total_qty'),
                DB::raw('SUM(isi.quantity * COALESCE(cost_attr.value, price_attr.value, 0)) as total_value')
            )
            ->groupBy('isi.source_code');

        if (!$user->isAdmin()) {
            $query->whereIn('isi.source_code', $user->getAssignedSourceCodes());
        }

        return response()->json($query->get());
    }

    public function movementSummary(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $query = StockMovement::select(
                'source_code',
                'type',
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(qty_change) as total_qty_change')
            )
            ->groupBy('source_code', 'type', DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->limit(30);

        if (!$user->isAdmin()) {
            $query->whereIn('source_code', $user->getAssignedSourceCodes());
        }

        return response()->json($query->get());
    }

    public function alerts(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $query = ImsAlert::whereNull('resolved_at')->orderBy('current_qty', 'asc');

        if (!$user->isAdmin()) {
            $query->whereIn('source_code', $user->getAssignedSourceCodes());
        }

        return response()->json($query->paginate(50));
    }

    public function resolveAlert(Request $request, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user');
        $alert = ImsAlert::findOrFail($id);

        if (!$user->canAccessSource($alert->source_code)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $alert->update(['resolved_at' => now()]);
        return response()->json(['message' => 'Alert resolved']);
    }
}
