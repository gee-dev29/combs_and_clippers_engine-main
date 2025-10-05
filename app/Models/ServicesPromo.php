<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicesPromo extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'discount_amount', 'start_date', 'end_date', 'status'];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'status' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}