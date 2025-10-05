<?php

namespace App\Exports\WalletReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class WalletReportPdf implements FromView
{
    protected $wallets;

    public function __construct($wallets)
    {
        $this->wallets = $wallets;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.wallet.wallet-report-pdf', [
            'wallets' => $this->wallets,
        ]);
    }
}