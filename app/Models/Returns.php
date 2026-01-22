<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Returns extends Model
{
    //

    protected $table = "returns";

    /**
     * Get the store that owns the Returns
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id', 'id');
    }

    /**
     * Get all of the return_products for the Returns
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function return_products(): HasMany
    {
        return $this->hasMany(\App\Models\ReturnProduct::class, 'return_id', 'id');
    }
}
