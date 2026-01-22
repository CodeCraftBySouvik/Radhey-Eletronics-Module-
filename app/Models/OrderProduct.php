<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_name', 'product_image', 'product_slug', 'product_variation_id', 'price', 'offer_price', 'qty'];

    public function productDetails() {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    
    public function orders() {
        return $this->belongsTo('App\Models\Order', 'order_id', 'id');
    }

    

}
