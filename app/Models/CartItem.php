<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
	
    protected $fillable = ['cart_id', 'productID', 'productname', 'image_url', 'description', 'quantity', 'currency', 'price', 'total_cost', 'deliveryperiod'];
    protected $table = 'cart_items';
    

    function cart()
    {
        return $this->belongsTo(Cart::class, 'id', 'cart_id');
       
    }

    function productInfo()
    {
        return $this->hasOne(Product::class, 'id', 'productID');
        
    }

}
