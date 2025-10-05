<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingPayment extends Model
{
	protected $fillable = ['payment_type', 'amount', 'reference', 'initiated_by', 'currency', 'payment_gateway', 'payment_status', 'payment_settlement_status', 'payment_settlement_ref'];
    
}
