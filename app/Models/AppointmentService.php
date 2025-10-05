<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentService extends Model
{
    use HasFactory;


    protected $fillable = [
        'appointment_id',
        'service_id',
        'quantity',
        'price',
        'price',                
        'original_price',       
        'discount_amount',     
        'promo_applied',       
        'promo_id',
    ];

    /**
     * Relationships
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, "appointment_id");
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function promo()
    {
        return $this->belongsTo(ServicesPromo::class, 'promo_id');
    }
}