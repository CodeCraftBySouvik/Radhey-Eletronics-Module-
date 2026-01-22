<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Discount extends Model
{
    //
    protected $table = "discounts";

    /**
     * Get the store that owns the Discount
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id', 'id');
    }

    /**
     * Get the payment associated with the Discount
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payment(): HasOne
    {
        return $this->hasOne(\App\Models\Payment::class, 'discount_id', 'id');
    }
}
