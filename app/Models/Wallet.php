<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'wallet_number', 'currency', 'amount', 'unclaimed_amount', 'account_number', 'bank_code'];
    protected $table = 'wallets';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'bankcode', 'bank_code');
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
