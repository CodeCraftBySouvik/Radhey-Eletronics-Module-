<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    //

    protected $table = "journal";

    /**
     * Get the payment that owns the Journal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Payment::class, 'payment_id', 'id');
    }
}
