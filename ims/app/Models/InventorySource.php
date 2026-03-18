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

class InventorySource extends Model
{
    protected $table = 'inventory_source';
    protected $primaryKey = 'source_code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'source_code', 'name', 'enabled', 'description',
        'contact_name', 'email', 'phone', 'country_id',
        'region', 'city', 'street', 'postcode',
        'latitude', 'longitude',
    ];

    protected $casts = ['enabled' => 'boolean'];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventorySourceItem::class, 'source_code', 'source_code');
    }
}
