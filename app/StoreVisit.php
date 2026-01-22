<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreVisit extends Model
{
    protected $fillable = [
        'user_id', 'store_id', 'start_date', 'start_time', 'end_date', 'end_time', 'start_location', 'start_latitude', 'start_longitude', 'end_location', 'end_latitude', 'end_longitude'
    ];

    public function users() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function store_detail() {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }
}
