<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppointmentService extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }


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