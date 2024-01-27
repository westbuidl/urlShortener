<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'businessID',
        'businessname',
        'businessregnumber',
        'businessemail',
        'businessphonenumber',
        'product',
        'businessaddress',
        'country',
        'city',
        'state',
        'zipcode',
        'password'
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