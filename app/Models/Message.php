<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = ['thread_id', 'sender_id', 'receiver_id', 'message', 'attachment' ,'created_at', 'updated_at', 'deleted_at'];

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
