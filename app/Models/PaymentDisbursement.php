<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class PaymentDisbursement extends Model

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
    protected $fillable = ['order_id', 'transferRef', 'traceID', 'orderPaymentRef', 'fromAcc', 'toAcc', 'toAcc_bankcode', 'amount', 'narration', 'responseCode', 'responseMessage', 'statusMessage'];
    protected $table = 'payment_disbursements';

}
