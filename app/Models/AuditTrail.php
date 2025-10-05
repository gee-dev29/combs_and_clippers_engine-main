<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    protected $table ="audit_trails";
    protected $fillable = ['user_id', 'name', 'user_ip', 'mac_address', 'imei', 'event', 'before', 'after', 'location'];
    
}
