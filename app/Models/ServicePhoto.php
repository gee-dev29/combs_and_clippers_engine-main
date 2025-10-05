<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePhoto extends Model
{
    protected $fillable = ['image_url', 'service_id'];
}
