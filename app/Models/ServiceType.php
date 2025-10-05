<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function storeServiceTypes()
    {
        return $this->hasMany(StoreServiceType::class, 'service_type_id');
    }

    public function boothRentals()
    {
        return $this->hasMany(BoothRental::class, 'service_type_id');
    }

    public function interests()
    {
        return $this->hasMany(Interest::class, 'service_type_id');
    }
}