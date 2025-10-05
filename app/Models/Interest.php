<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'image_link', 'service_type_id'];

    protected $table = 'interests';


    public function serviceType()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

}