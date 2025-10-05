<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BillingHistory extends Model
{
    protected $fillable = ['user_subscription_id', 'merchant_id', 'invoice_number', 'billing_date', 'next_billing_date', 'status', 'currency', 'amount', 'plan'];

    public function formatted_billing_date()
    {
        return Carbon::parse($this->billing_date)->format('M d, Y');
    }

    public function formatted_next_billing_date()
    {
        return Carbon::parse($this->next_billing_date)->format('M d, Y');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id', 'id');
    }
}
