<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SocialProvider extends Model
{
	protected $table ="social_providers";
    protected $fillable = ['user_id', 'provider_id','provider', 'nickname', 'avatar', 'access_token'];


    function user()
    {
        return $this->belongsTo(User::class);
    }
}
