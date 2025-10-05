<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = 
    [
    'user_id', 
    'notify_new_order_via_email',  
    'notify_new_order_via_sms',  
    'notify_new_order_via_push_notification',
    ];

    protected $table = 'notification_settings';

    protected $casts = [
        'notify_new_order_via_email'=> 'boolean',
        'notify_new_order_via_sms'=> 'boolean',
        'notify_new_order_via_push_notification'=> 'boolean',
    ];

    
}
