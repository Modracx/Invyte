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

class PurchaseOrderItem extends Model
{
    protected $table = 'ims_purchase_order_items';
    public $timestamps = false;

    protected $fillable = [
        'po_id', 'sku', 'product_name', 'qty_ordered',
        'qty_received', 'unit_cost',
    ];

    protected $casts = [
        'qty_ordered' => 'float',
        'qty_received' => 'float',
        'unit_cost' => 'float',
    ];
}
