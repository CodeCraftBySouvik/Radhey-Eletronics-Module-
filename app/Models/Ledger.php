<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ledger extends Model
{
    //

    protected $table = "ledger";
    protected $fillable = [
        'user_type', 'staff_id', 'store_id', 'supplier_id', 'admin_id', 'payment_id', 'staff_commision_id', 'collection_staff_commission_id', 'store_bad_debt_id', 'transaction_id', 'transaction_amount', 'is_credit', 'is_debit', 'bank_cash', 'entry_date', 'purpose', 'purpose_description', 'is_gst', 'start_date', 'whatsapp_status', 'last_whatsapp', 'created_at', 'updated_at'
    ];
    
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
