<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DayCashEntry extends Model
{
    protected $table = "day_cash_entry";
    protected $fillable = [
        'staff_id',
        'type',
        'payment_date',
        'amount',
        'payment_cash',
    ];

    public function staff()
  {
      return $this->belongsTo(User::class, 'staff_id', 'id');
  }
}
