<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'ims_suppliers';
    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'name', 'code', 'contact_name', 'email',
        'phone', 'address', 'country_id', 'city', 'notes', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function purchaseOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'supplier_id', 'supplier_id');
    }
}
