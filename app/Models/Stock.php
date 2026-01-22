<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stock extends Model
{
    

    protected $table = "stock";

    /**
     * Get the purchase_order that owns the Stock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase_order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    /**
     * Get the returns that owns the Stock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function returns(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Returns::class, 'return_id', 'id');
    }

    /**
     * Get all of the stock_product for the Stock
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stock_product(): HasMany
    {
        return $this->hasMany(\App\Models\StockProduct::class, 'stock_id', 'id');
    }
}
