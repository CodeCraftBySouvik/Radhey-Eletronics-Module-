<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingslipProduct extends Model
{
    //

    protected $table = "packing_slip";
    // protected $table = "packingslip_products";

    /**
     * Get the product that owns the PackingslipProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

    /**
     * Get the packingslip that owns the PackingslipProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function packingslip(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PackingslipNew1::class, 'packingslip_id', 'id');
    }
}
