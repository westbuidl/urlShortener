<?php

namespace App\Models;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class CompanyBuyer extends Model
{
    use HasFactory,HasApiTokens, Notifiable;
    protected $fillable = [
        'companyBuyerId',
        'companyname',
        'companyregnumber',
        'companyemail',
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
