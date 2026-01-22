<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentCollection extends Model
{
    protected $table = "payment_collections";

    /**
     * Get the users that owns the PaymentCollection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }

    /**
     * Get the admins that owns the PaymentCollection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admins(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'admin_id', 'id');
    }

    /**
     * Get the stores that owns the PaymentCollection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stores(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id', 'id');
    }
}
