<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Appointment extends Model
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
        'store_id',
        'customer_id',
        'merchant_id',
        'date',
        'time',
        'phone_number',
        'payment_details',
        'tip',
        'total_amount',
        'discount_amount', 
        'processing_fee',
        'status',
        'address_id',
        'payment_gateway',
        'cancel_reason',       
        'cancelled_by',         
        'cancelled_at',
        'payment_url',
        'payment_ref',
        'appointment_ref',
        'currency',
        'booking_type',
        'payment_status',
        'disbursement_status',
        'reason_for_cancelation',
        'merchant_confirmed_at',
        'client_confirmed_at',
        'merchant_confirmed_by',
        'client_confirmed_by'

    ];

        public function scopePublicBookings($query)
    {
        return $query->where('booking_type', 'public');
    }

    public function scopeAuthenticatedBookings($query)
    {
        return $query->where('booking_type', 'authenticated');
    }
    
    public function store()
    {
        return $this->belongsTo(Store::class, "store_id");
    }

    public function customer()
    {
        return $this->belongsTo(User::class, "customer_id");
    }

    public function serviceProvider()
    {
        return $this->belongsTo(User::class, "merchant_id");
    }

    public function appointmentService()
    {
        return $this->hasMany(AppointmentService::class, "appointment_id");
    }
    function virtualAccount()
    {
        return $this->hasOne(TransactionAccount::class, 'appointment_id', 'id');
    }
}