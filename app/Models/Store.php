<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['merchant_id', 'store_code', 'store_name', 'store_category', 'store_sub_category', 'website', 'store_banner', 'store_icon', 'store_description', 'featured', 'refund_allowed', 'replacement_allowed', 'approved', 'days_available', 'time_available', 'store_link'];

    protected $casts = [
        'refund_allowed' => 'boolean',
        'replacement_allowed' => 'boolean',
        'approved' => 'boolean',
        'featured' => 'boolean',
        // newly added
        'rewards' => 'array',
        'payment_preferences' => 'array',
        'booking_preferences' => 'array',
        'availability' => 'array',
        'booking_limits' => 'array',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function category()
    {
        return $this->belongsTo(StoreCategory::class, 'store_category');
    }

    public function subCategory()
    {
        return $this->belongsTo(StoreSubCategory::class, 'store_sub_category');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function pickupAddress()
    {
        return $this->hasOneThrough(PickupAddress::class, User::class, 'id', 'merchant_id', 'merchant_id', 'id');
    }

    public function storeAddress()
    {
        return $this->hasOneThrough(StoreAddress::class, User::class, 'id', 'merchant_id', 'merchant_id', 'id');
    }

    public function services()
    {
        return $this->hasMany(Service::class); 
    }

    public function workdoneImages()
    {
        return $this->hasMany(StoreWorkdoneImage::class, 'stores_id');
    }

    public function serviceTypes()
    {
        return $this->hasMany(StoreServiceType::class, 'store_id');
    }

    public function renters()
    {
        return $this->hasMany(
            UserStore::class,
            'store_id'
        );
    }

    public function bookings()
    {
        return $this->hasMany(Appointment::class, 'store_id');
    }

    public function boothRent()
    {
        return $this->hasMany(BoothRental::class, 'store_id');
    }

    public function lowestPricedService()
    {
        return $this->services()->orderBy('price', 'asc')->first();
    }

    public function storeVisits()
    {
        return $this->hasMany(StoreVisit::class, 'store_id');
    }

}