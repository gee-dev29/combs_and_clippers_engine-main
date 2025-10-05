<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionAccount extends Model
{
    protected $fillable = ['appointment_id', 'account_number', 'account_name', 'initiationTranRef', 'status', 'bank_code', 'account_id', 'provider', 'expiresAt', 'amount', 'processing_fee', 'total', 'amount_paid', 'order_id', 'booth_rental_payment_id'];
    protected $table = 'transaction_accounts';

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function boothRentPayment()
    {
        return $this->belongsTo(BoothRentalPayment::class);
    }
}