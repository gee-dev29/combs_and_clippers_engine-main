<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingAddress extends Model
{
    protected $fillable = ['name', 'recipient', 'street', 'city', 'state', 'postal_code', 'country', 'zip', 'address', 'phone', 'longitude', 'latitude', 'email', 'formatted_address', 'address_code', 'city_code', 'state_code', 'country_code'];
    protected $table = 'billing_addresses';
}
