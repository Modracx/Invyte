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

class InventorySourceItem extends Model
{
    protected $table = 'inventory_source_item';
    protected $primaryKey = 'source_item_id';
    public $timestamps = false;

    protected $fillable = ['source_code', 'sku', 'quantity', 'status'];

    protected $casts = ['quantity' => 'float', 'status' => 'integer'];
}
