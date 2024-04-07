<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;



    protected $fillable = [
        'userID',
        'productID',
        'orderID',
        'productName',
        'productDescription',
        'amount',
        'quantity',
        'paymentMethod',
        'Discount',
        'shippingFee',
        'status'
        
    ];

    public function IndividualAccount(){
        return $this->belongsTo('App\IndividualAccount');
    }
}
