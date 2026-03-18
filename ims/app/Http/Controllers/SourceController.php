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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user');

        $query = InventorySource::query();

        if (!$user->isAdmin()) {
            $query->whereIn('source_code', $user->getAssignedSourceCodes());
        }

        return response()->json($query->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'source_code' => 'required|string|max:255|unique:inventory_source,source_code',
            'name' => 'required|string|max:255',
            'enabled' => 'boolean',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:64',
            'country_id' => 'nullable|string|size:2',
            'city' => 'nullable|string|max:255',
            'street' => 'nullable|string|max:255',
            'postcode' => 'nullable|string|max:64',
        ]);

        $source = InventorySource::create($request->only([
            'source_code', 'name', 'enabled', 'description',
            'contact_name', 'email', 'phone', 'country_id',
            'region', 'city', 'street', 'postcode', 'latitude', 'longitude',
        ]));

        return response()->json($source, 201);
    }

    public function update(Request $request, string $code): JsonResponse
    {
        $source = InventorySource::findOrFail($code);

        $this->validate($request, [
            'name' => 'sometimes|string|max:255',
            'enabled' => 'sometimes|boolean',
        ]);

        $source->update($request->only([
            'name', 'enabled', 'description',
            'contact_name', 'email', 'phone', 'country_id',
            'region', 'city', 'street', 'postcode', 'latitude', 'longitude',
        ]));

        return response()->json($source);
    }

    public function destroy(string $code): JsonResponse
    {
        $source = InventorySource::findOrFail($code);
        $source->delete();
        return response()->json(['message' => 'Source deleted']);
    }
}
