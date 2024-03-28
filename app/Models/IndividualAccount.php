<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class IndividualAccount extends Authenticatable implements MustVerifyEmail
{
    use HasFactory,HasApiTokens, Notifiable;

    protected $fillable = [
        'userID',
        'firstname',
        'lastname',
        'email',
        'phone',
        'product',
        'profile',
        'country',
        'state',
        'city',
        'zipcode',
        'password',
        'profile_photo',
        'verification_code'
    ];
    public function orders(){
        return $this->hasMany('App\Order');
    }
}
