<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'productId',
        'buyerId',
        'product_name',
        'product_category',
        'product_image',
        'selling_price',
        'quantity',
        'total_price',
        'categoryID',
        'cartId'


        
    ];
   

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId'); // 'productId' is the foreign key column
    }
}
