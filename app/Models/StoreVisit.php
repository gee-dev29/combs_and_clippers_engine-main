<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreVisit extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'merchant_id',
        'store_id',
        'visitor_ip'
    ];
}
