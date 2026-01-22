<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    //
    protected $table = "invoice";

    /**
     * Get the order that owns the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Get the store that owns the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    /**
     * Get the user that owns the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'id');
    }

    /**
     * Get the packingslip that owns the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function packingslip(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PackingslipNew1::class, 'packingslip_id', 'id');
    }

    /**
     * Get all of the products for the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(\App\Models\InvoiceProduct::class, 'invoice_id', 'id');
    }
}
