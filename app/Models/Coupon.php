<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'merchant_id', 'code', 'discount', 'discount_type', 'end_date', 'start_date', 'limit'
    ];

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function isActive()
    {
        $today = now()->format('Y-m-d');
        return $this->start_date <= $today && $this->end_date >= $today;
    }

    public function getDiscountedAmount($amount)
    {
        if ($this->discount_type === 'F') {
            $newAmount = $amount - $this->discount;
        } elseif ($this->discount_type === 'P') {
            $newAmount = $amount - (($this->discount / 100) * $amount);
        } else {
            $newAmount = $amount;
        }
        return $newAmount;
    }

    public function getCouponValue($amount)
    {
        if ($this->discount_type === 'F') {
            $value = $this->discount;
        } else {
            $value = ($this->discount / 100) * $amount;
        }
        return $value;
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function hasReachedLimit()
    {
        return !is_null($this->limit) && $this->usages()->count() >= $this->limit;
    }
}
