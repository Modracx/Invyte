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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SalesOrder extends Model
{
    protected $table = 'sales_order';
    protected $primaryKey = 'entity_id';
    public $incrementing = true;
    public $timestamps = false;

    protected $casts = [
        'grand_total'       => 'float',
        'total_qty_ordered' => 'float',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'order_id', 'entity_id')
            ->whereNull('parent_item_id');
    }

    public function fulfillment(): HasOne
    {
        return $this->hasOne(Fulfillment::class, 'order_id', 'entity_id')
            ->latest();
    }
}
