<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'productId',
        'sellerId',
        'product_name',
        'categoryID',
        'product_category',
        'buyerId',
        'buyer_fullname',
        'rating',
        'feedback',
        'date'



    ];
    public function buyer()
{
    return $this->belongsTo(Buyer::class, 'buyerId', 'id');
}
}
