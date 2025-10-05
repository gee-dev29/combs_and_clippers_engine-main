<?php

namespace App\Exports\WalletReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WalletReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $wallets;

    public function __construct($wallets)
    {
        $this->wallets = $wallets;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.wallet.wallet-report-excel', [
            'wallets' => $this->wallets,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
            'A' => ['width' => 5],             // Adjust column widths as needed
            'B' => ['width' => 25],
            'C' => ['width' => 20],
            'D' => ['width' => 15],
            'E' => ['width' => 15],
            'F' => ['width' => 20],
            'G' => ['width' => 25],
            'H' => ['width' => 15],
            'I' => ['width' => 20],
            'J' => ['width' => 20],
        ];
    }
}