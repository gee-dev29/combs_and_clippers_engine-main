<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class NotificationSetting extends Model

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }
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
