<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'withdrawal_id',
        'seller_id',
        'seller_type',
        'amount',
        'bank_name',
        'account_name',
        'account_number',
        'status',
        'initiated_at',
        'completed_at',
    ];
}
