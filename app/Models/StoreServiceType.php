<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreServiceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'service_type_id'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function storeUsers()
    {
        return $this->hasMany(UserStore::class, 'service_type_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'service_type_id');
    }


}