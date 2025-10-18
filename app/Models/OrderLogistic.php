<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class OrderLogistic extends Model

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
    protected $fillable = ['pickup_order_id', 'fulfilment_request_id', 'channel_grouping_id', 'channel_reference_id', 'order_id', 'cart_id', 'pickup_address_id', 'delivery_address_id', 'redis_key', 'rate_id', 'get_rates_key', 'kwik_key', 'delivery_note', 'type', 'estimated_days', 'currency', 'amount', 'delivery_status'];
    protected $table = 'order_logistics';
}
