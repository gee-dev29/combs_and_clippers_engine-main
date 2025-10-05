<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'model',
        'model_uid',
        'merchant_id',
        'buyer_id',
        'description',
        'controller',
        'action',
        'params',
        'before_action',
        'after_action'
    ];

    protected $casts = [
        'params' => 'array',
        'before_action' => 'array',
        'after_action' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'buyer_id')->select([
            "name",
            "phone",
            "email",
            "profile_image_link"
        ]);
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }
}
