<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Rappasoft\LaravelAuthenticationLog\Traits\AuthenticationLoggable;

class Admin extends Authenticatable
{
    use Notifiable;
    use HasFactory;
    use AuthenticationLoggable;

    protected $guard = 'admin';

    protected $fillable = [
        'email',
        'name',
        'password',
        'accounttype',
        'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];
}
