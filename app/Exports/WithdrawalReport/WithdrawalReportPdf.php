<?php

namespace App\Exports\WithdrawalReport;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class WithdrawalReportPdf implements FromView
{
    protected $withdrawals;

    public function __construct($withdrawals)
    {
        $this->withdrawals = $withdrawals;
    }

    public function view(): View
    {
        return view('exports.withdrawal-report-pdf', [
            'withdrawals' => $this->withdrawals,
        ]);
    }
}