<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class Cart extends Model

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
{
    protected $fillable = ['buyer_id', 'merchant_id', 'totalprice', 'status', 'items_count', 'currency', 'shipping', 'total_sum', 'max_delivery_period', 'min_delivery_period', 'max_delivery_date', 'min_delivery_date', 'delivery_type', 'coupon_id'];

    protected $table = 'carts';

    const UNFULFILLED = 0;
    const FULFILLED   = 1;
    const PROCESSED   = 2;

    function cartItems()
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'id');
    }

    public function customer()
    {
        return $this->hasOne(User::class, 'id', 'buyer_id');
    }

    public function merchant()
    {
        return $this->hasOne(User::class, 'id', 'merchant_id');
    }
}
