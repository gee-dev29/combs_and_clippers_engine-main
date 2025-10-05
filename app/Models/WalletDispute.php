<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletDispute extends Model
{
    const OPEN = 0;
    const ACCEPTED = 1;
    const CLOSED = 2;
    const REJECTED = 3;
    const PROCESSING = 4;
    protected $fillable = ['user_id', 'wallet_id', 'wallet_transaction_id', 'transaction_reference', 'dispute_description', 'status', 'dispute_proof'];
    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function transaction()
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
