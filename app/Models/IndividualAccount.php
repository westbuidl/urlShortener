<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class IndividualAccount extends Model
{
    use HasFactory,HasApiTokens, Notifiable;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'phone',
        'product',
        'country',
        'state',
        'city',
        'zipcode',
        'password'
        //'password',
    ];
}
