<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductRequest extends Model
{
    use HasFactory;
    protected $fillable = ['product_name', 'product_category', 'email', 'product_link', 'additional_info'];
}
