<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'buyerId',
        'product_name',
        'product_category',
        'selling_price',
        'quantity',
        'categoryID'


        
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
