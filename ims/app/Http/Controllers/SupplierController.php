<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 20), 100);
        $query = Supplier::query();
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }
        return response()->json($query->orderBy('name')->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:64|unique:ims_suppliers,code',
            'email' => 'nullable|email',
        ]);

        $supplier = Supplier::create($request->only([
            'name', 'code', 'contact_name', 'email', 'phone',
            'address', 'country_id', 'city', 'notes', 'is_active',
        ]));

        return response()->json($supplier, 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Supplier::findOrFail($id));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $this->validate($request, [
            'code' => "sometimes|string|max:64|unique:ims_suppliers,code,{$id},supplier_id",
            'email' => 'nullable|email',
        ]);
        $supplier->update($request->only([
            'name', 'code', 'contact_name', 'email', 'phone',
            'address', 'country_id', 'city', 'notes', 'is_active',
        ]));
        return response()->json($supplier);
    }

    public function destroy(int $id): JsonResponse
    {
        Supplier::findOrFail($id)->delete();
        return response()->json(['message' => 'Supplier deleted']);
    }
}
