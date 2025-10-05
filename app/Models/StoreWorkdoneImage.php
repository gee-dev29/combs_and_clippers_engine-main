<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreWorkdoneImage extends Model
{
    use HasFactory;

    protected $fillable = ['stores_id', 'user_id', 'image_url'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}