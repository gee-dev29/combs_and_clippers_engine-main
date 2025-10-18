<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class PaymentTransaction extends Model

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
    protected $fillable = ['user_id', 'trans_id', 'cust_id', 'cust_email', 'cust_auth_code', 'cust_code', 'trans_ref', 'last_four_digit', 'amount', 'channel', 'card_type', 'currency', 'trans_status', 'gateway_res', 'ip', 'paid_at_res', 'created_at_res'];

    protected $table = 'payment_transactions';
    
}
