<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'attributes', 'price', 'quantity', 'inStock'];
    protected $table = 'product_variants';
    protected $casts = [
        'attributes' => 'array',
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
