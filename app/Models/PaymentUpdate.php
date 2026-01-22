<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentUpdate extends Model
{
    //
    protected $table = "payment_updates";

    /**
     * Get the store that owns the PaymentUpdate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id', 'id');
    }

    /**
     * Get the user that owns the PaymentUpdate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }

    /**
     * Get the payment that owns the PaymentUpdate
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Payment::class, 'payment_id', 'id');
    }
}
