<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Store;
use App\User;

class StoreNote extends Model
{
    //

    protected $table = "store_notes";

    /**
     * Get the store associated with the StoreNote
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
