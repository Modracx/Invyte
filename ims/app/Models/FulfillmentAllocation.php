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

class FulfillmentAllocation extends Model
{
    protected $table = 'ims_fulfillment_allocations';

    protected $fillable = [
        'fulfillment_id', 'order_item_id', 'sku',
        'source_code', 'location_id', 'qty_allocated',
    ];

    protected $casts = [
        'qty_allocated' => 'float',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(SourceLocation::class, 'location_id');
    }
}
