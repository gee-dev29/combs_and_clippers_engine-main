<?php

namespace App\Exports\BankDetailsReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BankDetailsReportPdf implements FromView
{
    protected $userBankDetails;

    public function __construct($userBankDetails)
    {
        $this->userBankDetails = $userBankDetails;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.bankDetails.bank-details-report-pdf', [
            'userBankDetails' => $this->userBankDetails,
        ]);
    }
}