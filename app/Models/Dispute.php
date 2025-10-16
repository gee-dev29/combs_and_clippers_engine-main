<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class Dispute extends Model

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
    const TYPE_REFUND = 'Refund';
    const TYPE_REPLACEMENT = 'Replace';

    const OPEN = 0;
    const PROCESSING = 1;
    const CLOSED_REFUNDED = 2;
    const CLOSED_REPLACED = 3;
    const REJECTED = 4;
    const ACCEPTED = 5;

    protected $fillable = ['order_id', 'dispute_referenceid', 'merchant_id', 'customer_id', 'customer_email', 'merchant_email', 'dispute_category', 'dispute_description', 'dispute_option', 'dispute_status', 'comment', 'resolution_date'];
    protected $table = 'disputes';

    public function disputeHearing()
    {
        return $this->hasMany(DisputeResolution::class, 'dispute_id', 'id');
    }

    public function disputeFiles()
    {
        return $this->hasMany(DisputeFile::class, 'dispute_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
