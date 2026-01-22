<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackingslipNew1 extends Model
{
    //

    protected $table = 'packingslips';

    /**
     * Get the store that owns the PackingslipNew1
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id', 'id');
    }
    
    /**
     * Get the order that owns the PackingslipNew1
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id', 'id');
    }

    /**
     * Get the invoice that owns the PackingslipNew1
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'invoice_id', 'id');
    }

    /**
     * Get all of the packingslip_products for the PackingslipNew1
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packingslip_products(): HasMany
    {
        return $this->hasMany(\App\Models\PackingslipProduct::class, 'packingslip_id', 'id');
    }
}
