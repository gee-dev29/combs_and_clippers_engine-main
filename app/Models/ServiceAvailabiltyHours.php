<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAvailabiltyHours extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'day', 'start_time', 'end_time'];

    protected $casts = [
        "start_time" => "string",
        'end_time' => "string"
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}