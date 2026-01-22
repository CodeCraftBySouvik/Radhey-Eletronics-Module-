<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ledger extends Model
{
    //

    protected $table = "ledger";
    
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id', 'id');
    }

    /**
     * Get the staff that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'staff_id', 'id');
    }

    /**
     * Get the partner that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'admin_id', 'id');
    }

    /**
     * Get the supplier that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id', 'id');
    }

    /**
     * Get the payment that owns the Ledger
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Payment::class, 'payment_id', 'id');
    }
}
