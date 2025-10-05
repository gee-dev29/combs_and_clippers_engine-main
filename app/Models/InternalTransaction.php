<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalTransaction extends Model
{
    use HasFactory;
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
