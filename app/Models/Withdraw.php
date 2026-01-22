<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdraw extends Model
{
    //
    protected $table = "withdrawls";

    /**
     * Get the partner that owns the Withdraw
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id', 'admin_id');
    }
}
