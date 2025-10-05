<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreCategory extends Model
{
    use HasFactory;
    protected $fillable = ['categoryname'];

    public function subCategories()
    {
        return $this->hasMany(StoreSubCategory::class);
    }
}
