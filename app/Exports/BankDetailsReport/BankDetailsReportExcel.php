<?php

namespace App\Exports\BankDetailsReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BankDetailsReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $userBankDetails;

    public function __construct($userBankDetails)
    {
        $this->userBankDetails = $userBankDetails;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.bankDetails.bank-details-report-excel', [
            'userBankDetails' => $this->userBankDetails,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
            'A' => ['width' => 5],             // Adjust column widths as needed
            'B' => ['width' => 25],
            'C' => ['width' => 15],
            'D' => ['width' => 25],
            'E' => ['width' => 20],
            'F' => ['width' => 20],
            'G' => ['width' => 15],
            'H' => ['width' => 15],
            'I' => ['width' => 20],
        ];
    }
}