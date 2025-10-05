<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['bank', 'shortname', 'bankcode', 'sortcode', 'vfd_bankcode'];
    protected $table = 'banks';
    public $timestamps = false;

    const WEMA = '035';
    
}