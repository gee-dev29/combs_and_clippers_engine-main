<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = ['wallet_id', 'withdrawal_id', 'type', 'transaction_ref', 'narration', 'currency', 'amount', 'status', 'from_account_no', 'from_account_name', 'from_bank_name', 'from_bank_code', 'to_account_no', 'to_account_name', 'to_bank_name', 'to_bank_code'];
    const SUCCESSFUL = 'successful';
    const PENDING = 'pending';
    const FAILED = 'failed';

    function hasDispute()
    {
        return $this->hasOne(WalletDispute::class, 'wallet_transaction_id', 'id')->count() > 0;
    }

    public function withdrawal()
    {
        return $this->belongsTo(Withdrawal::class, 'withdrawal_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
        ;
    }
}