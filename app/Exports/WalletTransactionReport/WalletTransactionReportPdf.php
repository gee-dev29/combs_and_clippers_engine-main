<?php

namespace App\Exports\WalletTransactionReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class WalletTransactionReportPdf implements FromView
{
    protected $walletTransactions;

    public function __construct($walletTransactions)
    {
        $this->walletTransactions = $walletTransactions;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.walletTransaction.wallet-transaction-report-pdf', [
            'walletTransactions' => $this->walletTransactions,
        ]);
    }
}