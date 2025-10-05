<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestPayment extends Model
{
	 protected $fillable = ['customer_id', 'trans_ref', 'category', 'description'];
    
}
