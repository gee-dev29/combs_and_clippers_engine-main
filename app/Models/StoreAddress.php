<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreAddress extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'email', 'phone', 'street', 'city', 'state', 'country', 'zip', 'longitude', 'latitude', 'address', 'merchant_id', 'formatted_address', 'address_code', 'city_code', 'state_code', 'country_code', 'postal_code'];
    protected $table = 'store_addresses';
}
