<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class Buyer extends Authenticatable implements MustVerifyEmail
{
    use HasFactory,HasApiTokens, Notifiable;

    protected $fillable = [
        'buyerId',
        'firstname',
        'lastname',
        'email',
        'phone',
        'country',
        'state',
        'city',
        'address',
        'zipcode',
        'password',
        'profile_photo',
        'verification_code'
    ];

    //protected $primaryKey = 'userID';

    public function orders(){
        return $this->hasMany('App\Order');
    }
}
