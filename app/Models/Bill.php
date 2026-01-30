<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $table = "bills";
    protected $fillable = [
        'store_id',
        'invoice_id',
        'service_slip_id', 
        'transaction_id', 
        'entry_date', 
        'amount'
    ];
}
