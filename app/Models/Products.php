<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Testing\Fakes\BusFake;
use Laravel\Sanctum\HasApiTokens;

class Products extends Model
{
    //use HasFactory;
    

    use HasFactory,HasApiTokens, Notifiable;
    

    protected $fillable = [
        'product_id',
        'user_id',
        'product_name',
        'product_category',
        'selling_price',
        'cost_price',
        'quantityin_stock',
        'unit',
        'product_description',
        'product_image',
        'is_active',
        'categoryID'


        
    ];

    public function individuals(){
        return $this->belongsTo(IndividualAccount::class);
    }

    public function business(){
        return $this->belongsTo(BusinessAccount::class);
    }

    public function products(){
        return $this->belongsTo(Products::class);
    }
}
