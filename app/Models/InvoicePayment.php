<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    //
    protected $table = "invoice_payments";

    /**
     * Get the invoice that owns the InvoicePayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'invoice_id', 'id');
    }

    /**
     * Get the paymentcollection that owns the InvoicePayment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentcollection(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PaymentCollection::class, 'payment_collection_id', 'id');
    }
}
