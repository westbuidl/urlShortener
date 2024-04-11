<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Buyer extends Model
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
