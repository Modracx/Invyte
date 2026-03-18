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

class SourceLocation extends Model
{
    protected $table = 'ims_source_locations';

    protected $fillable = ['source_code', 'parent_id', 'type', 'name', 'code', 'sort_order'];

    protected $casts = ['sort_order' => 'integer'];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Recursively load children up to 3 levels deep (row → shelf → column).
     */
    public function childrenDeep(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->children()->with('childrenDeep');
    }

    public function itemLocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SourceItemLocation::class, 'location_id');
    }

    /** Full label: "Row A / Shelf 1" built by walking ancestry. */
    public function getFullPathAttribute(): string
    {
        $parts = [$this->name];
        $node  = $this;
        while ($node->parent_id) {
            $node    = $node->parent;
            $parts[] = $node->name;
        }
        return implode(' / ', array_reverse($parts));
    }
}
