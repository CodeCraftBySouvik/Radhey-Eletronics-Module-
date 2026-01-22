<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = ['cat_id', 'name', 'description', 'image_path', 'slug'];
    
    public function category() {
        return $this->belongsTo('App\Models\Category', 'cat_id', 'id');
    }
}
