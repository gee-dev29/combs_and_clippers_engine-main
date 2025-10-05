<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    protected $fillable = ['user_id', 'wallet_id', 'amount', 'unclaimed_amount', 'account_number', 'account_name', 'bank_name', 'bank_code', 'transferRef', 'withdrawal_status', 'amount_requested', 'fee', 'narration', 'is_internal'];
    const SUCCESSFUL = 1;
    const PENDING = 0;
    const FAILED = 2;
    const PROCESSING = 3;

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'id', 'wallet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
