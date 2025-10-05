<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\UserStore;
use App\Models\BoothRental;
use App\Models\BoothRentPaymentHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BoothRentalPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_store_id',
        'booth_rental_id',
        'last_payment_date',
        'next_payment_date',
        'payment_status',
        'amount',
        'processing_fee'
    ];

    protected $casts = [
        'next_payment_date' => 'datetime',
        'last_payment_date' => 'datetime',
    ];


    public function getPaymentStatusAttribute($value)
    {
        $today = Carbon::today();
        $nextPaymentDate = Carbon::parse($this->next_payment_date);

        if ($nextPaymentDate->isToday()) {
            return 'due';
        } elseif ($nextPaymentDate->gt($today) && $nextPaymentDate->lte($today->copy()->addDays(5))) {
            return 'upcoming';
        } elseif ($nextPaymentDate->isPast()) {
            return 'overdue';
        }

        return $value;
    }



    public function userStore()
    {
        return $this->belongsTo(UserStore::class, 'user_store_id');
    }

    public function boothRental()
    {
        return $this->belongsTo(BoothRental::class, 'booth_rental_id');
    }

    public function paymentHistories()
    {
        return $this->hasMany(BoothRentPaymentHistory::class, 'booth_rent_payment_id');
    }

    function virtualAccount()
    {
        return $this->hasOne(TransactionAccount::class, 'booth_rental_payment_id', 'id');
    }

}