<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Category extends Model


{
   
    use HasFactory;

    protected $fillable = [
        'categoryID',
        'categoryName',
        'categoryDescription',
        'categoryImage',
        'quantity_instock',
        'quantity_sold'
        
    ];


    public function images()
    {
        return $this->hasMany(Category::class);
    }
    // Define the relationship with products
    public function products()
    {
        return $this->hasMany(Products::class);
    }
    
}
