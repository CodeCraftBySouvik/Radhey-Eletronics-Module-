<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnBox extends Model
{
    //

    protected $table = "return_boxes";

    /**
     * Get the product that owns the ReturnBox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

    /**
     * Get the returns that owns the ReturnBox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function returns(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Returns::class, 'return_id', 'id');
    }
}
