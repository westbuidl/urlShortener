<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
   
    protected $fillable = [
        'sessionID',
        'userID',
        'productID',
        'quantity',
        'source'
        
    ];
}
