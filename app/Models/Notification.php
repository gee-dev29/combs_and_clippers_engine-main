<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notification extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'view_link', 'viewed', 'type'];
    protected $table = 'user_notifications';


    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }


}