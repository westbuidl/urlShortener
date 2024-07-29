<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'wishlistId',
        'buyerId',
        'productId',
        'product_image',
        'product_name',
        'product_category',
        'selling_price',
        'categoryID',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId', 'productId');
    }
}
