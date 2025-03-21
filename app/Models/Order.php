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
        'grand_price',
        'phone_number',
        'sellerId',
        'sellerFullname',
        'sellerEmail',
        'sellerPhone',
        
    ];

    public function IndividualAccount(){
        return $this->belongsTo('App\IndividualAccount');
    }
    public function orderItems()
{
    return $this->hasMany(Order::class, 'orderId', 'id');
}
public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyerID');
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'sellerID');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productID');
    }
   /* public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id'); // Check if 'order_id' is correct
    }*/
    
}