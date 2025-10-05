<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'available_status',
        'service_type_id',
        'current'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function userStoreServiceType()
    {
        return $this->belongsTo(StoreServiceType::class, 'service_type_id');
    }

    public function boothRentPayment()
    {
        return $this->hasOne(BoothRentalPayment::class, 'user_store_id');
    }
}