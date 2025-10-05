<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGrowServiceProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'create_profile_link',
        'setup_referal_reward',
        'setup_loyalty_reward',
        'schedule_protection',
    ];
}