<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['name', 'currency', 'currencycode'];
    protected $table = 'currencies';
    public $timestamps = false;
}
