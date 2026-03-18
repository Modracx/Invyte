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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    protected $table = 'sales_order_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $casts = [
        'qty_ordered'  => 'float',
        'qty_shipped'  => 'float',
        'qty_canceled' => 'float',
        'price'        => 'float',
    ];

    /**
     * Qty still needing fulfillment = ordered - shipped - canceled
     */
    public function getQtyToFulfillAttribute(): float
    {
        return max(0, ($this->qty_ordered ?? 0) - ($this->qty_shipped ?? 0) - ($this->qty_canceled ?? 0));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'order_id', 'entity_id');
    }
}
