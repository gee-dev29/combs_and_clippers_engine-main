<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InternalTransaction extends Model
{
    use HasFactory;

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
    protected $fillable = ['merchant_id', 'customer_id', 'order_id', 'type', 'transaction_ref', 'narration', 'currency', 'amount', 'payment_status'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id')->select([
            "name",
            "phone",
            "email",
            "profile_image_link"
        ]);
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id')->select([
            "name",
            "phone",
            "email",
            "profile_image_link"
        ]);
    }
}
