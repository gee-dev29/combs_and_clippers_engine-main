<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class BillingHistory extends Model

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
    protected $fillable = ['user_subscription_id', 'merchant_id', 'invoice_number', 'billing_date', 'next_billing_date', 'status', 'currency', 'amount', 'plan'];

    public function formatted_billing_date()
    {
        return Carbon::parse($this->billing_date)->format('M d, Y');
    }

    public function formatted_next_billing_date()
    {
        return Carbon::parse($this->next_billing_date)->format('M d, Y');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id', 'id');
    }
}
