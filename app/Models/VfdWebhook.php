<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VfdWebhook extends Model
{
    protected $fillable = ['reference', 'amount', 'from_account_no', 'from_account_name', 'from_bankcode', 'narration', 'session_id', 'trans_date', 'account_no'];
    protected $table = 'vfd_webhooks';
    
}
