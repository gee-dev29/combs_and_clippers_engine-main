<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Thread extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = ['user_id', 'recipient','last_message_at','created_at', 'updated_at', 'deleted_at'];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient');
    }
}
