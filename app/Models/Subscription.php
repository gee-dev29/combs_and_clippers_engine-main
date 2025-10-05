<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    const TRIAL = 1;
    const WEEKLY = 2;
    const MONTHLY = 3;

    const STATUS = [
        "PENDING" => "Pending",
        "FAILED_TXN" => "Failed",
        "SUCCESSFUL_TXN" => "Successful",
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_subscriptions', 'subscription_id', 'user_id');
    }

    public function isFree()
    {
        return $this->price == 0.00;
    }

    public function hasFreeTrial()
    {
        return $this->trial_period > 0;
    }
}
