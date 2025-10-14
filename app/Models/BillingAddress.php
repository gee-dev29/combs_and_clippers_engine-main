<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class BillingAddress extends Model

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
{
    protected $fillable = ['name', 'recipient', 'street', 'city', 'state', 'postal_code', 'country', 'zip', 'address', 'phone', 'longitude', 'latitude', 'email', 'formatted_address', 'address_code', 'city_code', 'state_code', 'country_code'];
    protected $table = 'billing_addresses';
}
