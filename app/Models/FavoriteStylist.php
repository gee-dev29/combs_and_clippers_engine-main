<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteStylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stylist_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function stylist()
    {
        return $this->belongsTo(User::class, 'stylist_id');
    }
}