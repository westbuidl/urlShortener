<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Testing\Fakes\BusFake;
use Laravel\Sanctum\HasApiTokens;

class Product extends Model
{
    //use HasFactory;


    use HasFactory, HasApiTokens, Notifiable;


    protected $fillable = [
        'productId',
        'sellerId',
        'product_name',
        'product_category',
        'cost_price',
        'selling_price',
        'quantityin_stock',
        'quantity_sold',
        'unit',
        'productWeight',
        'product_description',
        'product_image',
        'is_active',
        'categoryID'



    ];

    public function sellers()
    {
        return $this->belongsTo(Seller::class);
    }

    public function business()
    {
        return $this->belongsTo(BusinessAccount::class);
    }

    public function products()
    {
        return $this->belongsTo(Product::class);
    }
    public function images()
    {
        return $this->hasMany(Product::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class,  'categoryID', 'id');
    }
    public function carts()
    {
        return $this->hasMany(Cart::class, 'productId', 'id'); // 'productId' is the foreign key column
    }
    public function Seller()
    {
        return $this->belongsTo(Seller::class, 'sellerId', 'sellerId');
    }
    public function orders()
    {
        return $this->belongsTo(Order::class, 'sellerId', 'sellerId');
    }
    public function wishlistItems()
    {
        return $this->hasMany(Wishlist::class, 'productId', 'productId');
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'id');
    }
}
