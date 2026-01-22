<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockProduct extends Model
{
    protected $table = "stock_products";

    /**
     * Get the product that owns the StockProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

    /**
     * Get the stock that owns the StockProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stock::class, 'stock_id', 'id');
    }
}
