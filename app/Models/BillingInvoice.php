<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingInvoice extends Model
{
    use HasFactory;
    protected $fillable = ['merchant_id', 'invoice_number', 'billing_date', 'next_billing_date', 'status', 'currency', 'amount', 'plan'];

    public $timestamps = false;

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
