<?php

namespace App\Exports\InternalTransactionReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InternalTransactionReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $internalTransactions;
    protected $totalTransactions;
    protected $totalAmount;
    protected $averageAmount;

    public function __construct($internalTransactions, $totalTransactions, $totalAmount, $averageAmount)
    {
        $this->internalTransactions = $internalTransactions;
        $this->totalTransactions = $totalTransactions;
        $this->totalAmount = $totalAmount;
        $this->averageAmount = $averageAmount;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.internalTransaction.internalTransaction-report-excel', [
            'internalTransactions' => $this->internalTransactions,
            'totalTransactions' => $this->totalTransactions,
            'totalAmount' => $this->totalAmount,
            'averageAmount' => $this->averageAmount,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
            'A' => ['width' => 5],             // Adjust column widths as needed
            'B' => ['width' => 25],
            'C' => ['width' => 25],
            'D' => ['width' => 15],
            'E' => ['width' => 25],
            'F' => ['width' => 30],
            'G' => ['width' => 10],
            'H' => ['width' => 15],
            'I' => ['width' => 15],
            'J' => ['width' => 20],
        ];
    }
}