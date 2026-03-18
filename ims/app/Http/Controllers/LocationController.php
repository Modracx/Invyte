<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Http\Controllers;

use App\Models\InventorySource;
use App\Models\SourceItemLocation;
use App\Models\SourceLocation;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // -----------------------------------------------------------------------
    // Location tree management (per source)
    // -----------------------------------------------------------------------

    /**
     * GET /sources/{code}/locations
     * Returns the full tree: rows → shelves → columns.
     */
    public function tree(Request $request, string $code): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        if (!$user->isAdmin() && !in_array($code, $user->getAssignedSourceCodes(), true)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        InventorySource::findOrFail($code);

        $rows = SourceLocation::where('source_code', $code)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with('childrenDeep')
            ->get();

        return response()->json($rows);
    }

    /**
     * POST /sources/{code}/locations
     * Creates a row, shelf, or column dynamically.
     */
    public function store(Request $request, string $code): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        if (!$user->isAdmin() && !in_array($code, $user->getAssignedSourceCodes(), true)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        InventorySource::findOrFail($code);

        $this->validate($request, [
            'parent_id'  => 'nullable|integer|exists:ims_source_locations,id',
            'name'       => 'required|string|max:100',
            'code'       => 'required|string|max:50',
            'sort_order' => 'nullable|integer',
        ]);

        $parentId = $request->input('parent_id');
        $type     = $this->resolveType($parentId, $code);

        if ($type === null) {
            return response()->json(['message' => 'Maximum nesting depth is 3 levels (Row → Shelf → Column)'], 422);
        }

        $location = SourceLocation::create([
            'source_code' => $code,
            'parent_id'   => $parentId,
            'type'        => $type,
            'name'        => $request->input('name'),
            'code'        => $request->input('code'),
            'sort_order'  => $request->input('sort_order', 0),
        ]);

        return response()->json($location, 201);
    }

    /**
     * PUT /sources/{code}/locations/{id}
     */
    public function update(Request $request, string $code, int $id): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        if (!$user->isAdmin() && !in_array($code, $user->getAssignedSourceCodes(), true)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $location = SourceLocation::where('source_code', $code)->findOrFail($id);

        $this->validate($request, [
            'name'       => 'sometimes|string|max:100',
            'code'       => 'sometimes|string|max:50',
            'sort_order' => 'sometimes|integer',
        ]);

        $location->update($request->only(['name', 'code', 'sort_order']));

        return response()->json($location);
    }

    /**
     * DELETE /sources/{code}/locations/{id}
     * Cascades to child locations and item assignments.
     */
    public function destroy(string $code, int $id): JsonResponse
    {
        $location = SourceLocation::where('source_code', $code)->findOrFail($id);
        $location->delete();

        return response()->json(['message' => 'Location deleted']);
    }

    // -----------------------------------------------------------------------
    // Item location assignment (per product)
    // -----------------------------------------------------------------------

    /**
     * GET /products/{id}/locations
     * Returns all location assignments for a product, grouped by source_code.
     * Also returns flat location lists per source for the picker.
     */
    public function productLocations(Request $request, int $id): JsonResponse
    {
        $user    = $request->attributes->get('auth_user');
        $product = Product::where('entity_id', $id)->firstOrFail();

        // All source codes that carry stock for this SKU
        $sourceItemsQuery = \App\Models\InventorySourceItem::where('sku', $product->sku);
        if (!$user->isAdmin()) {
            $sourceItemsQuery->whereIn('source_code', $user->getAssignedSourceCodes());
        }
        $sourceCodes = $sourceItemsQuery->pluck('source_code')->unique()->toArray();

        // All current assignments (multiple per source now possible)
        $rows = SourceItemLocation::where('sku', $product->sku)
            ->whereIn('source_code', $sourceCodes)
            ->with(['location', 'location.parent', 'location.parent.parent'])
            ->get();

        // Group assignments by source_code  →  [ source_code => [ {id, location_id, path}, … ] ]
        $assignmentsBySource = [];
        foreach ($rows as $sil) {
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

            $assignmentsBySource[$sil->source_code][] = [
                'id'          => $sil->id,
                'location_id' => $sil->location_id,
                'qty'         => (float) $sil->qty,
                'path'        => $fullPath,
                'name'        => $loc?->name,
                'type'        => $loc?->type,
            ];
        }

        // Flat location list per source (for search + checkbox picker)
        $flatBySource = [];
        foreach ($sourceCodes as $sc) {
            $flatBySource[$sc] = SourceLocation::where('source_code', $sc)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'type', 'parent_id'])
                ->toArray();
        }

        // Available stock qty per source (for the qty distribution UI)
        $stockBySource = [];
        foreach ($sourceCodes as $sc) {
            $stockBySource[$sc] = (float) \App\Models\InventorySourceItem::where('source_code', $sc)
                ->where('sku', $product->sku)
                ->value('quantity') ?? 0;
        }

        return response()->json([
            'assignments'   => $assignmentsBySource,   // { source_code: [{id, location_id, qty, path}] }
            'locations'     => $flatBySource,          // { source_code: [{id, name, code, type, parent_id}] }
            'stock'         => $stockBySource,         // { source_code: total_qty }
        ]);
    }

    /**
     * PUT /products/{id}/location
     * Replace the full set of location assignments for one source.
     * Accepts { source_code, location_ids: [1,2,3] }  — empty array clears all.
     */
    public function assignLocation(Request $request, int $id): JsonResponse
    {
        $user    = $request->attributes->get('auth_user');
        $product = Product::where('entity_id', $id)->firstOrFail();

        $this->validate($request, [
            'source_code'          => 'required|string',
            'locations'            => 'present|array',
            'locations.*.location_id' => 'required|integer|exists:ims_source_locations,id',
            'locations.*.qty'         => 'required|numeric|min:0',
        ]);

        $sourceCode = $request->input('source_code');
        $locations  = $request->input('locations', []);   // [{location_id, qty}]

        if (!$user->canAccessSource($sourceCode)) {
            return response()->json(['message' => 'Access denied to this source'], 403);
        }

        // Verify every location belongs to this source
        $locationIds = array_column($locations, 'location_id');
        if (!empty($locationIds)) {
            $valid   = SourceLocation::whereIn('id', $locationIds)
                ->where('source_code', $sourceCode)
                ->pluck('id')
                ->toArray();
            $invalid = array_diff($locationIds, $valid);
            if (!empty($invalid)) {
                return response()->json(['message' => 'Some locations do not belong to source ' . $sourceCode], 422);
            }
        }

        // Validate total qty doesn't exceed source inventory
        $totalAssigned = array_sum(array_column($locations, 'qty'));
        $available     = \App\Models\InventorySourceItem::where('source_code', $sourceCode)
            ->where('sku', $product->sku)
            ->value('quantity') ?? 0;

        if ($totalAssigned > $available + 0.0001) {
            return response()->json([
                'message' => "Total assigned qty ({$totalAssigned}) exceeds available stock ({$available}) in source '{$sourceCode}'",
            ], 422);
        }

        // Replace all assignments for this sku+source
        \Illuminate\Support\Facades\DB::transaction(function () use ($product, $sourceCode, $locations) {
            SourceItemLocation::where('sku', $product->sku)
                ->where('source_code', $sourceCode)
                ->delete();

            $seen = [];
            foreach ($locations as $loc) {
                $locId = (int) $loc['location_id'];
                if (isset($seen[$locId])) continue;  // skip duplicates
                $seen[$locId] = true;

                SourceItemLocation::create([
                    'source_code' => $sourceCode,
                    'sku'         => $product->sku,
                    'location_id' => $locId,
                    'qty'         => (float) $loc['qty'],
                ]);
            }
        });

        $count = count($locations);
        return response()->json([
            'message' => $count > 0 ? "{$count} location(s) assigned" : 'All location assignments removed',
        ]);
    }

    // -----------------------------------------------------------------------
    // Flat list of leaf locations for dropdowns
    // GET /sources/{code}/locations/flat
    // -----------------------------------------------------------------------

    public function flat(Request $request, string $code): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        if (!$user->isAdmin() && !in_array($code, $user->getAssignedSourceCodes(), true)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $locations = SourceLocation::where('source_code', $code)
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn($l) => [
                'id'        => $l->id,
                'type'      => $l->type,
                'name'      => $l->name,
                'code'      => $l->code,
                'parent_id' => $l->parent_id,
            ]);

        return response()->json($locations);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function resolveType(?int $parentId, string $sourceCode): ?string
    {
        if ($parentId === null) {
            return 'row';
        }

        $parent = SourceLocation::find($parentId);
        if (!$parent || $parent->source_code !== $sourceCode) {
            return null;
        }

        return match ($parent->type) {
            'row'   => 'shelf',
            'shelf' => 'column',
            default => null,
        };
    }
}
