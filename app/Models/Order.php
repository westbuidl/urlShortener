<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;



    protected $fillable = [
        'buyerId',
        'productId',
        'orderId',
        'productName',
        'productImage',
        'amount',
        'quantity',
        'paymentMethod',
        'paymentReference',
        'Discount',
        'shippingFee',
        'order_status',
        'grand_price'
        
    ];

    public function IndividualAccount(){
        return $this->belongsTo('App\IndividualAccount');
    }
}
