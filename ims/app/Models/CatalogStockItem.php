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

class CatalogStockItem extends Model
{
    protected $table = 'cataloginventory_stock_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'stock_id', 'qty', 'min_qty',
        'is_in_stock', 'manage_stock', 'notify_stock_qty',
    ];

    protected $casts = [
        'qty' => 'float',
        'is_in_stock' => 'integer',
        'manage_stock' => 'integer',
    ];
}
