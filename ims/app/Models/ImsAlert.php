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

class ImsAlert extends Model
{
    protected $table = 'ims_alerts';

    protected $fillable = [
        'sku', 'source_code', 'threshold', 'current_qty', 'resolved_at',
    ];

    protected $casts = [
        'threshold' => 'float',
        'current_qty' => 'float',
        'resolved_at' => 'datetime',
    ];
}
