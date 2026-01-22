<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderProduct extends Model
{
    //

    protected $table = "purchase_order_products";

    /**
     * Get the product that owns the PurchaseOrderProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

    public function purchase_orders(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PurchaseOrder::class, 'purchase_order_id', 'id');
    }

     /**
     * Get all of the purchase_order_box for the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchase_order_box(): HasMany
    {
        return $this->hasMany(\App\Models\PurchaseOrderProduct::class, 'purchase_order_id', 'id');
    }
}
