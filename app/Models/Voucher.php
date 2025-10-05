<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'stylist_id',
        'user_id',
        'code',
        'discount',
        'expiry_date',
        'is_used'
    ];

    public function stylist()
    {
        return $this->belongsTo(User::class, 'stylist_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}