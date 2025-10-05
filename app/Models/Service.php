<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use \Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
    protected $fillable = ['merchant_id', 'name', 'description', 'slug', 'price_type', 'price', 'currency', 'image_url', 'status', 'duration', 'buffer', 'payment_preference', 'deposit', 'location', 'home_service_charge', 'allow_cancellation', 'allowed_cancellation_period', 'allow_rescheduling', 'allowed_rescheduling_period', 'booking_reminder', 'booking_reminder_period', 'limit_early_booking', 'early_booking_limit_period', 'limit_late_booking', 'late_booking_limit_period', 'checkout_label', 'created_at', 'updated_at', 'is_available', 'store_id'];
    protected $casts = [
        'is_available' => 'boolean',
    ];
    protected $table = 'services';


    public function merchant()
    {
        return $this->hasOne(User::class, 'id', 'merchant_id');
    }

    public function photos()
    {
        return $this->hasMany(ServicePhoto::class, 'service_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function availabilityHours()
    {
        return $this->hasMany(ServiceAvailabiltyHours::class);
    }


    public function promotions()
    {
        return $this->hasMany(ServicesPromo::class);
    }

    public function appointment()
    {
        return $this->hasMany(AppointmentService::class, 'service_id');
    }
}