<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $fillable = ['user_id', 'trans_id', 'cust_id', 'cust_email', 'cust_auth_code', 'cust_code', 'trans_ref', 'last_four_digit', 'amount', 'channel', 'card_type', 'currency', 'trans_status', 'gateway_res', 'ip', 'paid_at_res', 'created_at_res'];

    protected $table = 'payment_transactions';
    
}
