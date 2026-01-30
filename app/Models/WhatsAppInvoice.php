<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppInvoice extends Model
{
    //
    protected $table = "whatsapp_invoices";
    protected $fillable = [
       'invoice_id', 'order_id', 'store_id', 'user_id', 'packingslip_id', 'invoice_no', 'net_price', 'required_payment_amount', 'payment_status', 'is_paid', 'store_address_outstation', 'trn_file', 'is_gst', 'tally_bill_file', 'tb_required', 'transport_lr_file', 'lr_required', 'status', 'last_whatsapp', 'created_by', 'created_at', 'updated_by', 'updated_at'
    ];

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
