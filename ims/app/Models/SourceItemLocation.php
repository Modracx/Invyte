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

class SourceItemLocation extends Model
{
    protected $table = 'ims_source_item_locations';

    protected $fillable = ['source_code', 'sku', 'location_id', 'qty'];

    protected $casts = ['qty' => 'float'];

    public function location(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SourceLocation::class, 'location_id');
    }
}
