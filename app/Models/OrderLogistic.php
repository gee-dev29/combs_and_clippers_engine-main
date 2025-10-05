<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLogistic extends Model
{
    protected $fillable = ['pickup_order_id', 'fulfilment_request_id', 'channel_grouping_id', 'channel_reference_id', 'order_id', 'cart_id', 'pickup_address_id', 'delivery_address_id', 'redis_key', 'rate_id', 'get_rates_key', 'kwik_key', 'delivery_note', 'type', 'estimated_days', 'currency', 'amount', 'delivery_status'];
    protected $table = 'order_logistics';
}
