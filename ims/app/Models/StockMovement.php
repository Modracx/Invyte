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

class StockMovement extends Model
{
    protected $table = 'ims_stock_movements';
    const UPDATED_AT = null;

    protected $fillable = [
        'source_code', 'sku', 'type', 'qty_before',
        'qty_after', 'qty_change', 'reason', 'user_id', 'reference_id',
    ];

    protected $casts = [
        'qty_before' => 'float',
        'qty_after' => 'float',
        'qty_change' => 'float',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
