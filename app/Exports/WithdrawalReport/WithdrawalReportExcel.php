<?php

namespace App\Exports\WithdrawalReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WithdrawalReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $withdrawals;

    public function __construct($withdrawals)
    {
        $this->withdrawals = $withdrawals;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.withdrawal.withdrawal-report-excel', [
            'withdrawals' => $this->withdrawals,
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
            'F' => ['width' => 15],
            'G' => ['width' => 25],
            'H' => ['width' => 25],
            'I' => ['width' => 20],
            'J' => ['width' => 20],
            'K' => ['width' => 15],
            'L' => ['width' => 30],
            'M' => ['width' => 10],
            'N' => ['width' => 20],
        ];
    }
}