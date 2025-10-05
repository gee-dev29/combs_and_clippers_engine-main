<?php

namespace App\Exports\WalletTransactionReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WalletTransactionReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $walletTransactions;

    public function __construct($walletTransactions)
    {
        $this->walletTransactions = $walletTransactions;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.walletTransaction.wallet-transaction-report-excel', [
            'walletTransactions' => $this->walletTransactions,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
            'A' => ['width' => 5],             // Adjust column widths as needed
            'B' => ['width' => 25],
            'C' => ['width' => 20],
            'D' => ['width' => 20],
            'E' => ['width' => 15],
            'F' => ['width' => 25],
            'G' => ['width' => 25],
            'H' => ['width' => 10],
            'I' => ['width' => 20],
            'J' => ['width' => 30],
            'K' => ['width' => 20],
        ];
    }
}