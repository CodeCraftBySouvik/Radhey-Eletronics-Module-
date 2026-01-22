<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['cat_id', 'sub_cat_id', 'name', 'short_desc', 'desc', 'cost_price', 'sell_price', 'unit_value', 'unit_type', 'image'];

    public function category() {
        return $this->belongsTo('App\Models\Category', 'cat_id', 'id');
    }

    public function subCategory() {
        return $this->belongsTo('App\Models\SubCategory', 'sub_cat_id', 'id');
    }

    /**
     * Get all of the count_stock for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function count_stock(): HasMany
    {
        return $this->hasMany(\App\Models\StockBox::class, 'product_id', 'id')->where('is_scanned', 0)->where('is_stock_out', 0);
    }

    
}
