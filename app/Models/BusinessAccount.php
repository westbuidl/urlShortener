<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class BusinessAccount extends Model
{
    use HasFactory,HasApiTokens, Notifiable;
    protected $fillable = [
        'businessID',
        'businessname',
        'businessregnumber',
        'businessemail',
        'businessphone',
        'products',
        'businessaddress',
        'country',
        'city',
        'state',
        'zipcode',
        'password',
        'profile_photo',
        'verification_code'
    ];
}
/*'businessID',
        'businessname',
        'businessregnumber',
        'businessemail',
        'businessphonenumber',
        'products',
        'businessaddress',
        'country',
        'city',
        'state',
        'zipcode',
        'password'*/