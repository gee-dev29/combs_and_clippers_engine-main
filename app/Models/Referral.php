<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = ['referrer_id', 'customer_id', 'customer_type', 'claim_status'];
    protected $table = 'referrals';


    public function customer()
    {
        return $this->hasOne(User::class, 'id', 'customer_id');
    }

}
