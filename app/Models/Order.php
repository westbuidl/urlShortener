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
        'channel',
        'payment_id',
        'country_code',
        'customer_email',
        'shipping_address',
        'firstname',
        'lastname',
        'billing_address',
        'grand_price'
        
    ];

    public function IndividualAccount(){
        return $this->belongsTo('App\IndividualAccount');
    }
}