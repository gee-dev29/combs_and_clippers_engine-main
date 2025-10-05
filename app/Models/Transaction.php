<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const SUCCESSFUL = 'successful';
    const PENDING = 'pending';
    const FAILED = 'failed';
    protected $fillable = ['posting_date', 'type', 'transcode', 'tempcode', 'customer_email', 'merchant_email', 'merchant_code', 'description', 'amount', 'country', 'currency', 'startdate', 'enddate', 'fulfill_days', 'payment_gateway', 'payment_date', 'payment_status', 'trans_status', 'refunddate', 'releasedate', 'stoprefunddate', 'refunded', 'extended', 'requestextend', 'reqestrefund', 'confirmed_by_merchant', 'confirmed_date', 'cancelled_date', 'insert_date', 'amountpaid', 'fufill_notice_date', 'paystack_fee', 'RAVE_fee', 'request_extend', 'stop_payment_date', 'reason_for_stopping', 'refund_date', 'reason_for_stop_refund', 'stop_refund_date', 'arbitration_request_date', 'order_id', 'request_refund', 'appid'];
    protected $table = 'transactions';
    //public $timestamps = false;

    function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function customer()
    {
        return $this->hasOne(User::class, 'email', 'customer_email')->select([
            "name",
            "phone",
            "email",
            "profile_image_link"
        ]);
    }
}
