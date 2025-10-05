<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'merchant_id', 'discount_name', 'discount_type', 'discount', 'start_date', 'end_date'
    ];

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'discount_products', 'discount_id', 'product_id')->withTimestamps();
    }
}
