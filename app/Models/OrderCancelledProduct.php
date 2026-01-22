<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderCancelledProduct extends Model
{
    protected $table = "order_cancelled_products";

    /**
     * Get the order that owns the OrderCancelledProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(User::class, 'order_id', 'id');
    }

    /**
     * Get the product that owns the OrderCancelledProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(User::class, 'foreign_key', 'id');
    }
}
