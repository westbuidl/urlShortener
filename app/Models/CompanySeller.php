<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanySeller extends Model
{
    use HasFactory,HasApiTokens, Notifiable;
    protected $fillable = [
        'companySellerId',
        'companyname',
        'companyregnumber',
        'companyemail',
        'product',
        'product_category',
        'companyphone',
        'companyaddress',
        'country',
        'city',
        'state',
        'zipcode',
        'password',
        'profile_photo',
        'verification_code'
    ];
}
