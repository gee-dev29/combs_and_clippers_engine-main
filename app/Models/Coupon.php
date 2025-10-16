<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }
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
