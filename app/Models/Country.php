<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['country'];
    protected $table = 'countries';
    public $timestamps = false;   
}