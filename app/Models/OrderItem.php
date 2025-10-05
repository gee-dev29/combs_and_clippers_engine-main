<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'productname', 'price', 'quantity', 'totalCost', 'image', 'productID'];
    protected $table = 'order_items';

    function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    function customer()
    {
        return $this->hasOneThrough(User::class, Order::class, 'id', 'id', 'order_id', 'buyer_id')
            ->select([
                "name",
                "phone",
                "email",
                "profile_image_link"
            ]);
    }

    function productInfo()
    {
        return $this->hasOne(Product::class, 'id', 'productID');
    }


    public function productTrnx()
    {
        return $this->hasManyThrough(Transaction::class, Order::class, 'id', 'order_id', 'order_id', 'id')->select(['posting_date', 'trans_status', 'amount']);
    }

    public function orderTransaction()
    {
        return $this->hasOne(Transaction::class, 'order_id', 'order_id');
    }
}
