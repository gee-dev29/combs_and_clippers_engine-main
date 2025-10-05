<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoothRentPaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'booth_rent_payment_id',
        'amount_paid',
        'payment_date'
    ];

    public function boothRentPayment()
    {
        return $this->belongsTo(BoothRentalPayment::class, 'booth_rent_payment_id', 'id');
    }
}