<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    protected $fillable = ['tracking_code', 'issues', 'payurl', 'paymentRef', 'externalRef', 'delivery_type', 'payment_gateway', 'orderRef', 'maxdeliverydate', 'mindeliverydate', 'merchant_id', 'buyer_id', 'address_id', 'totalprice', 'shipping', 'status', 'total', 'currency', 'payment_status', 'disbursement_status', 'cart_id', 'confirmation_pin', 'confirmation_pin_expires_at', 'cancellation_reason', 'coupon_id'];
    protected $table = 'orders';
    protected $hidden = ['confirmation_pin_expires_at'];

    const CANCELED = 0;
    const UNPAID = 1;
    const PAID = 2;
    const PROCESSING = 3;
    const SHIPPED = 4;
    const DELIVERED = 5;
    const COMPLETED = 6;
    const DISPUTED = 7;
    const REFUNDED = 8;
    const REPLACED = 9;
    const IN_REVIEW = 10;

    const ORDER_SHIPPING_FEE = 8000;


    const PAYMENT_UNSUCCESSFUL = 0;
    const PAYMENT_SUCCESSFUL = 1;

    const TYPE_DELIVERY = 'Delivery';
    const TYPE_PICKUP = 'Pickup';

    const DISPUTE_NOT_ALLOWED = [
        self::UNPAID => 'Dispute cannot be opened on an unpaid order',
        self::CANCELED => 'Dispute cannot be opened on a canceled order',
        self::DISPUTED => 'Order has already been disputed',
        self::REFUNDED => 'Dispute cannot be opened on a refunded order',
        self::REPLACED => 'Dispute cannot be opened on a replaced order',
        self::PROCESSING => 'Dispute cannot be opened as order is being processed or out for delivery',
        self::SHIPPED => 'Dispute cannot be opened as order is being processed or out for delivery',
    ];

    const CANCEL_NOT_ALLOWED = [
        self::UNPAID => 'An unpaid order cannot be canceled',
        self::CANCELED => 'Order already canceled',
        self::SHIPPED => 'Order cannot be canceled as it has been shipped',
        self::DELIVERED => 'Order cannot be canceled as it has been delivered',
        self::COMPLETED => 'Order cannot be canceled as it has been completed',
        self::DISPUTED => 'Order cannot be canceled as it has been disputed',
        self::REFUNDED => 'Order cannot be canceled as it has been refunded',
        self::REPLACED => 'Order cannot be canceled as it has been replaced',
    ];

    const MARK_AS_DELIVERED_NOT_ALLOWED = [
        self::UNPAID => 'An unpaid order cannot be marked as delivered',
        self::CANCELED => 'A canceled order cannot be marked as delivered',
        self::DELIVERED => 'Order has already been marked as delivered',
        self::COMPLETED => 'Order has already been marked as completed',
        self::DISPUTED => 'A disputed order cannot be marked as delivered',
        self::REFUNDED => 'A refunded order cannot be marked as delivered',
    ];

    function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function orderTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id');
    }

    function disputes()
    {
        return $this->hasMany(Dispute::class, 'order_id', 'id');
    }

    public function orderPayment()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'id')->select(['transcode', 'description', 'amount', 'currency', 'startdate', 'enddate', 'payment_date', 'payment_status', 'trans_status', 'payment_gateway']);
    }

    public function orderLogistics()
    {
        return $this->hasOne(OrderLogistic::class, 'order_id', 'id');
    }

    public function orderAddress()
    {
        return $this->hasOne(Address::class, 'id', 'address_id');
    }

    public function pickupAddress()
    {
        return $this->hasOneThrough(PickupAddress::class, User::class, 'id', 'merchant_id', 'merchant_id', 'id');
    }

    public function store()
    {
        return $this->hasOneThrough(Store::class, User::class, 'id', 'merchant_id', 'merchant_id', 'id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'buyer_id')->select([
            "name",
            "phone",
            "email",
            "profile_image_link"
        ]);
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id')->select([
            "name",
            "phone",
            "email",
            "profile_image_link"
        ]);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function scopeDisputed($query, $merchant_id)
    {
        return $query->where(['status' => Order::DISPUTED, 'merchant_id' => $merchant_id])->latest();
    }

    public function scopeAll($query, $merchant_id)
    {
        return $query->whereIn('status',  [Order::DISPUTED, Order::REFUNDED, Order::REPLACED, Order::IN_REVIEW])->where(['merchant_id' => $merchant_id])->latest();
    }

    public function scopeRefunded($query, $merchant_id)
    {
        return $query->where(['status' => Order::REFUNDED, 'merchant_id' => $merchant_id])->latest();
    }

    public function scopeReplaced($query, $merchant_id)
    {
        return $query->where(['status' => Order::REPLACED, 'merchant_id' => $merchant_id])->latest();
    }

    public function scopeInreview($query, $merchant_id)
    {
        return $query->where(['status' => Order::IN_REVIEW, 'merchant_id' => $merchant_id])->latest();
    }

    public function humanReadableDate()
    {
        return Carbon::parse($this->created_at)->format("D jS \of M h:i:s A");
    }
}
