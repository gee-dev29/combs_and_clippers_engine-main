<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Str;

class CartItem extends Model

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
