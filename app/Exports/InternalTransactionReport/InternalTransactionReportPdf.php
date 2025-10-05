<?php

namespace App\Exports\InternalTransactionReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class InternalTransactionReportPdf implements FromView
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
        return view('comb_and_clippers_admin.exports.internalTransaction.internalTransaction-report-pdf', [
            'internalTransactions' => $this->internalTransactions,
            'totalTransactions' => $this->totalTransactions,
            'totalAmount' => $this->totalAmount,
            'averageAmount' => $this->averageAmount,
        ]);
    }
}