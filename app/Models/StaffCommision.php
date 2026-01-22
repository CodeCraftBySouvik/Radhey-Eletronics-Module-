<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffCommision extends Model
{
    //
    protected $table = "staff_commision";

    /**
     * Get the staff that owns the StaffCommision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'staff_id', 'id');
    }

    /**
     * Get the order that owns the StaffCommision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id', 'id');
    }

    /**
     * Get the invoice that owns the StaffCommision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'invoice_id', 'id');
    }

    /**
     * Get the invoice_payments that owns the StaffCommision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice_payments(): BelongsTo
    {
        return $this->belongsTo(\App\Models\InvoicePayment::class, 'invoice_payment_id', 'id');
    }
}
