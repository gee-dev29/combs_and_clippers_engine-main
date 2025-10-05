<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentDisbursement extends Model
{
    protected $fillable = ['order_id', 'transferRef', 'traceID', 'orderPaymentRef', 'fromAcc', 'toAcc', 'toAcc_bankcode', 'amount', 'narration', 'responseCode', 'responseMessage', 'statusMessage'];
    protected $table = 'payment_disbursements';

}
