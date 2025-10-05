<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoothRental extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'user_id', 'payment_timeline', 'amount', 'payment_days', 'service_type_id'];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function boothRentPayment()
    {
        return $this->hasMany(BoothRentalPayment::class, 'booth_rental_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }
}