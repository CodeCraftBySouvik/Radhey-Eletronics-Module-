<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAttendance extends Model
{
    protected $table = "user_attendances";
    protected $fillable = [
        'user_id','status','date', 'location','lat','lng','start_time','end_time'
    ];

    public function users() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
