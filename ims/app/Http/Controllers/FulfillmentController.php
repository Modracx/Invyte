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
use App\Models\Fulfillment;
use App\Models\Product;
use App\Models\FulfillmentAllocation;
use App\Models\InventorySourceItem;
use App\Models\SalesOrder;
use App\Models\SourceItemLocation;
use App\Models\SourceLocation;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FulfillmentController extends Controller
{
    // -------------------------------------------------------------------------
    // GET /api/orders
    // -------------------------------------------------------------------------
    public function orders(Request $request): JsonResponse
    {
        $user    = $request->attributes->get('auth_user');
        $perPage = min((int) $request->input('per_page', 20), 100);
        $status  = $request->input('status', '');
        $search  = $request->input('search', '');

        $query = SalesOrder::orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('increment_id', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $paginated = $query->paginate($perPage);

        // Attach latest fulfillment status for each order
        $orderIds = $paginated->pluck('entity_id')->toArray();
        $fulfillments = Fulfillment::whereIn('order_id', $orderIds)
            ->select('id', 'order_id', 'status', 'confirmed_at', 'created_at')
            ->get()
            ->groupBy('order_id')
            ->map(fn($group) => $group->sortByDesc('created_at')->first());

        $items = $paginated->getCollection()->map(function ($order) use ($fulfillments) {
            $order->fulfillment_status = $fulfillments->get($order->entity_id)?->status ?? null;
            $order->fulfillment_id     = $fulfillments->get($order->entity_id)?->id ?? null;
            return $order;
        });

        $paginated->setCollection($items);

        return response()->json($paginated);
    }

    // -------------------------------------------------------------------------
    // GET /api/orders/{id}
    // -------------------------------------------------------------------------
    public function orderDetail(Request $request, int $id): JsonResponse
    {
        $user  = $request->attributes->get('auth_user');
        $order = SalesOrder::findOrFail($id);
        $items = $order->items()->get();

        $skus = $items->pluck('sku')->filter()->unique()->values()->toArray();

        // Flat locations per source (for picker dropdowns)
        $allSourceCodes = InventorySourceItem::whereIn('sku', $skus)
            ->pluck('source_code')
            ->unique()
            ->toArray();

        if (!$user->isAdmin()) {
            $assigned       = $user->getAssignedSourceCodes();
            $allSourceCodes = array_values(array_intersect($allSourceCodes, $assigned));
        }

        // SKU → source_code → assigned locations (multiple per source now)
        $locationsBySkuSource = SourceItemLocation::whereIn('sku', $skus)
            ->with(['location', 'location.parent', 'location.parent.parent'])
            ->get()
            ->groupBy(fn($sil) => $sil->sku . '::' . $sil->source_code);

        // Build availability per SKU
        $availability = [];
        foreach ($skus as $sku) {
            $sourceItems = InventorySourceItem::where('sku', $sku)->get();

            if (!$user->isAdmin()) {
                $assigned    = $user->getAssignedSourceCodes();
                $sourceItems = $sourceItems->filter(fn($si) => in_array($si->source_code, $assigned));
            }

            $availability[$sku] = $sourceItems->map(function ($si) use ($sku, $locationsBySkuSource) {
                $key             = $sku . '::' . $si->source_code;
                $locAssignments  = $locationsBySkuSource[$key] ?? collect([]);

                $assignedLocations = $locAssignments->map(function ($sil) {
                    $loc      = $sil->location;
                    $fullPath = null;
                    if ($loc) {
                        $parts   = [$loc->name];
                        $current = $loc;
                        if ($current->parent) {
                            array_unshift($parts, $current->parent->name);
                            if ($current->parent->parent) {
                                array_unshift($parts, $current->parent->parent->name);
                            }
                        }
                        $fullPath = implode(' / ', $parts);
                    }
                    return [
                        'id'          => $sil->location_id,
                        'location_id' => $sil->location_id,
                        'name'        => $loc?->name,
                        'path'        => $fullPath,
                        'qty'         => (float) $sil->qty,
                    ];
                })->values();

                return [
                    'source_code'        => $si->source_code,
                    'qty_available'      => (float) $si->quantity,
                    'assigned_locations' => $assignedLocations,
                    'locations'          => $assignedLocations,  // dropdown: only assigned locations
                ];
            })->values();
        }

        // Load existing fulfillment (latest draft first, then confirmed)
        $fulfillment = Fulfillment::where('order_id', $id)
            ->with(['allocations.location', 'createdBy:id,name', 'confirmedBy:id,name'])
            ->orderByRaw("FIELD(status,'draft','confirmed')")
            ->orderBy('created_at', 'desc')
            ->first();

        // Append qty_to_fulfill accessor to each item
        $itemsData = $items->map(function ($item) {
            $arr                 = $item->toArray();
            $arr['qty_to_fulfill'] = $item->qty_to_fulfill;
            return $arr;
        });

        return response()->json([
            'order'        => $order,
            'items'        => $itemsData,
            'availability' => $availability,
            'fulfillment'  => $fulfillment,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/fulfillments
    // -------------------------------------------------------------------------
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 20), 100);
        $status  = $request->input('status', '');
        $search  = $request->input('search', '');

        $query = Fulfillment::with([
            'createdBy:id,name,email',
            'confirmedBy:id,name,email',
        ])->orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where('increment_id', 'like', "%{$search}%");
        }

        return response()->json($query->paginate($perPage));
    }

    // -------------------------------------------------------------------------
    // GET /api/fulfillments/{id}
    // -------------------------------------------------------------------------
    public function show(Request $request, int $id): JsonResponse
    {
        $fulfillment = Fulfillment::with([
            'allocations.location',
            'createdBy:id,name,email',
            'confirmedBy:id,name,email',
        ])->findOrFail($id);

        $order = SalesOrder::find($fulfillment->order_id);

        return response()->json([
            'fulfillment' => $fulfillment,
            'order'       => $order,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /api/fulfillments
    // -------------------------------------------------------------------------
    public function store(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $this->validate($request, [
            'order_id'                        => 'required|integer',
            'notes'                           => 'nullable|string',
            'allocations'                     => 'required|array|min:1',
            'allocations.*.order_item_id'     => 'required|integer',
            'allocations.*.sku'               => 'required|string',
            'allocations.*.source_code'       => 'required|string',
            'allocations.*.location_id'       => 'nullable|integer',
            'allocations.*.qty_allocated'     => 'required|numeric|min:0.0001',
        ]);

        $orderId = (int) $request->input('order_id');
        $order   = SalesOrder::find($orderId);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Only one draft fulfillment at a time per order
        $existingDraft = Fulfillment::where('order_id', $orderId)
            ->where('status', 'draft')
            ->first();
        if ($existingDraft) {
            return response()->json([
                'message'        => 'A draft fulfillment already exists for this order. Please edit or delete it first.',
                'fulfillment_id' => $existingDraft->id,
            ], 422);
        }

        // Check source access
        $allocations = $request->input('allocations');
        foreach ($allocations as $alloc) {
            if (!$user->canAccessSource($alloc['source_code'])) {
                return response()->json([
                    'message' => "Access denied to source: {$alloc['source_code']}",
                ], 403);
            }
        }

        $fulfillment = DB::transaction(function () use ($request, $user, $order, $allocations) {
            $fulfillment = Fulfillment::create([
                'order_id'     => $order->entity_id,
                'increment_id' => $order->increment_id,
                'status'       => 'draft',
                'notes'        => $request->input('notes'),
                'created_by'   => $user->id,
            ]);

            foreach ($allocations as $alloc) {
                FulfillmentAllocation::create([
                    'fulfillment_id' => $fulfillment->id,
                    'order_item_id'  => $alloc['order_item_id'],
                    'sku'            => $alloc['sku'],
                    'source_code'    => $alloc['source_code'],
                    'location_id'    => $alloc['location_id'] ?? null,
                    'qty_allocated'  => $alloc['qty_allocated'],
                ]);
            }

            return $fulfillment->load('allocations');
        });

        return response()->json($fulfillment, 201);
    }

    // -------------------------------------------------------------------------
    // PUT /api/fulfillments/{id}
    // -------------------------------------------------------------------------
    public function update(Request $request, int $id): JsonResponse
    {
        $user        = $request->attributes->get('auth_user');
        $fulfillment = Fulfillment::findOrFail($id);

        if ($fulfillment->status !== 'draft') {
            return response()->json(['message' => 'Only draft fulfillments can be edited'], 422);
        }

        $this->validate($request, [
            'notes'                           => 'nullable|string',
            'allocations'                     => 'required|array|min:1',
            'allocations.*.order_item_id'     => 'required|integer',
            'allocations.*.sku'               => 'required|string',
            'allocations.*.source_code'       => 'required|string',
            'allocations.*.location_id'       => 'nullable|integer',
            'allocations.*.qty_allocated'     => 'required|numeric|min:0.0001',
        ]);

        $allocations = $request->input('allocations');

        foreach ($allocations as $alloc) {
            if (!$user->canAccessSource($alloc['source_code'])) {
                return response()->json([
                    'message' => "Access denied to source: {$alloc['source_code']}",
                ], 403);
            }
        }

        $fulfillment = DB::transaction(function () use ($request, $fulfillment, $allocations) {
            $fulfillment->update(['notes' => $request->input('notes')]);
            $fulfillment->allocations()->delete();

            foreach ($allocations as $alloc) {
                FulfillmentAllocation::create([
                    'fulfillment_id' => $fulfillment->id,
                    'order_item_id'  => $alloc['order_item_id'],
                    'sku'            => $alloc['sku'],
                    'source_code'    => $alloc['source_code'],
                    'location_id'    => $alloc['location_id'] ?? null,
                    'qty_allocated'  => $alloc['qty_allocated'],
                ]);
            }

            return $fulfillment->load('allocations');
        });

        return response()->json($fulfillment);
    }

    // -------------------------------------------------------------------------
    // POST /api/fulfillments/{id}/confirm
    // -------------------------------------------------------------------------
    public function confirm(Request $request, int $id): JsonResponse
    {
        $user        = $request->attributes->get('auth_user');
        $fulfillment = Fulfillment::with('allocations')->findOrFail($id);

        if ($fulfillment->status !== 'draft') {
            return response()->json(['message' => 'Only draft fulfillments can be confirmed'], 422);
        }

        // Group allocations by source_code + sku and sum qty
        $grouped = [];
        foreach ($fulfillment->allocations as $alloc) {
            $key = $alloc->source_code . '::' . $alloc->sku;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'source_code'  => $alloc->source_code,
                    'sku'          => $alloc->sku,
                    'qty_needed'   => 0,
                ];
            }
            $grouped[$key]['qty_needed'] += (float) $alloc->qty_allocated;
        }

        // Validate stock availability
        $errors = [];
        foreach ($grouped as $key => $group) {
            $sourceItem = InventorySourceItem::where('source_code', $group['source_code'])
                ->where('sku', $group['sku'])
                ->first();

            $available = $sourceItem ? (float) $sourceItem->quantity : 0;

            if ($available < $group['qty_needed']) {
                $errors[] = [
                    'source_code' => $group['source_code'],
                    'sku'         => $group['sku'],
                    'available'   => $available,
                    'needed'      => $group['qty_needed'],
                ];
            }
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Insufficient stock for one or more allocations',
                'errors'  => $errors,
            ], 422);
        }

        // Deduct stock and create movements
        DB::transaction(function () use ($fulfillment, $grouped, $user) {
            // Deduct per-location qtys for each allocation that has a location_id
            foreach ($fulfillment->allocations as $alloc) {
                if ($alloc->location_id) {
                    SourceItemLocation::where('source_code', $alloc->source_code)
                        ->where('sku', $alloc->sku)
                        ->where('location_id', $alloc->location_id)
                        ->update([
                            'qty' => DB::raw('GREATEST(0, qty - ' . (float) $alloc->qty_allocated . ')'),
                        ]);
                }
            }

            foreach ($grouped as $group) {
                $sourceItem = InventorySourceItem::where('source_code', $group['source_code'])
                    ->where('sku', $group['sku'])
                    ->lockForUpdate()
                    ->first();

                $qtyBefore = $sourceItem ? (float) $sourceItem->quantity : 0;
                $qtyAfter  = max(0, $qtyBefore - $group['qty_needed']);

                InventorySourceItem::where('source_code', $group['source_code'])
                    ->where('sku', $group['sku'])
                    ->update([
                        'quantity' => $qtyAfter,
                        'status'   => $qtyAfter > 0 ? 1 : 0,
                    ]);

                // Sync legacy cataloginventory_stock_item (keyed by product_id not sku)
                $totalQty = InventorySourceItem::where('sku', $group['sku'])->sum('quantity');
                $product  = Product::where('sku', $group['sku'])->first();
                if ($product) {
                    CatalogStockItem::where('product_id', $product->entity_id)->update([
                        'qty'         => $totalQty,
                        'is_in_stock' => $totalQty > 0 ? 1 : 0,
                    ]);
                }

                StockMovement::create([
                    'source_code' => $group['source_code'],
                    'sku'         => $group['sku'],
                    'type'        => 'fulfillment',
                    'qty_before'  => $qtyBefore,
                    'qty_after'   => $qtyAfter,
                    'qty_change'  => -$group['qty_needed'],
                    'reason'      => "Order #{$fulfillment->increment_id}",
                    'user_id'     => $user->id,
                    'reference_id' => $fulfillment->order_id,
                ]);
            }

            $fulfillment->update([
                'status'       => 'confirmed',
                'confirmed_by' => $user->id,
                'confirmed_at' => \Carbon\Carbon::now(),
            ]);
        });

        $fulfillment->refresh();

        return response()->json([
            'message'     => 'Fulfillment confirmed',
            'fulfillment' => $fulfillment->load(['allocations', 'confirmedBy:id,name']),
        ]);
    }

    // -------------------------------------------------------------------------
    // DELETE /api/fulfillments/{id}
    // -------------------------------------------------------------------------
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user        = $request->attributes->get('auth_user');
        $fulfillment = Fulfillment::findOrFail($id);

        if ($fulfillment->status !== 'draft') {
            return response()->json(['message' => 'Only draft fulfillments can be deleted'], 422);
        }

        if (!$user->isAdmin() && $fulfillment->created_by !== $user->id) {
            return response()->json(['message' => 'Access denied — you can only delete your own fulfillments'], 403);
        }

        $fulfillment->delete(); // cascades to allocations via FK

        return response()->json(['message' => 'Fulfillment deleted']);
    }
}
