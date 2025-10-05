<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'merchant_id', 'review_text', 'rating'];


    public function reviewer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }
}