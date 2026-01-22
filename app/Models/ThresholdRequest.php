<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\Order;

class ThresholdRequest extends Model
{
    protected $table = "product_threshold_request";

    /**
     * Get the user that owns the ThresholdRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the store that owns the ThresholdRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    /**
     * Get the product that owns the ThresholdRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the order that owns the ThresholdRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get the hold_order that owns the ThresholdRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function hold_order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'hold_order_id', 'id');
    }
}
