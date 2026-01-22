<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Notifications\Notifiable;

class Category extends Model
{
    // use Notifiable;

    protected $fillable = [
        'name', 'description', 'image_path', 'slug'
    ];

    public function subcategory()
    {
        return $this->hasMany(Subcategory::class,'cat_id');
    }

    public function parentCatDetails() {
        return $this->belongsTo('App\Models\CategoryParent', 'parent', 'id');
    }

    public function ProductDetails(string $orderBy = 'style_no', string $order = 'asc') {
        return $this->hasMany('App\Models\Product', 'cat_id', 'id')->where('status', 1)->orderBy($orderBy, $order);
    }
}
