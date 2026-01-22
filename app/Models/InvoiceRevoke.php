<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceRevoke extends Model
{
    protected $table = "invoice_revokes";

    /**
     * Get the store that owns the InvoiceRevoke
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id', 'id');
    }

    /**
     * Get the order that owns the InvoiceRevoke
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id', 'id');
    }
    
}
