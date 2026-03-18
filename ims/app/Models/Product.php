<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'catalog_product_entity';
    protected $primaryKey = 'entity_id';
    public $timestamps = false;

    protected $fillable = [];

    public function scopeWithName(Builder $query): Builder
    {
        $attrId = (int) env('MAGENTO_ATTR_NAME', 73);
        return $query->leftJoin(
            'catalog_product_entity_varchar as name_attr',
            function ($join) use ($attrId) {
                $join->on('catalog_product_entity.entity_id', '=', 'name_attr.entity_id')
                     ->where('name_attr.attribute_id', $attrId)
                     ->where('name_attr.store_id', 0);
            }
        )->addSelect('catalog_product_entity.*', 'name_attr.value as name');
    }

    public function scopeWithPrice(Builder $query): Builder
    {
        $attrId = (int) env('MAGENTO_ATTR_PRICE', 77);
        return $query->leftJoin(
            'catalog_product_entity_decimal as price_attr',
            function ($join) use ($attrId) {
                $join->on('catalog_product_entity.entity_id', '=', 'price_attr.entity_id')
                     ->where('price_attr.attribute_id', $attrId)
                     ->where('price_attr.store_id', 0);
            }
        )->addSelect('price_attr.value as price');
    }

    public function stockItem(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CatalogStockItem::class, 'product_id', 'entity_id');
    }

    public function sourceItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventorySourceItem::class, 'sku', 'sku');
    }
}
