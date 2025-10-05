<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBoothProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'add_schedule_location',
        'setup_my_service',
        'setup_portfolio',
        'create_bio',
        'accept_payment',
    ];
}