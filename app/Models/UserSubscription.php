<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'subscription_id','active','expires_at', 'ext_trans_id', 'internal_trans_id', 'status', 'auto_renew', 'customer', 'session', 'invoice', 'subscription',
    ];

    protected $casts = [
        'active'=> 'boolean',
        'auto_renew'=> 'boolean',
    ];
}
