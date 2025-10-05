<?php

namespace App\Exports\GeneralReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class GeneralReportPdf implements FromView
{
    protected $reportData;

    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    public function view(): View
    {
        $reportData = $this->reportData;

        return view('comb_and_clippers_admin.exports.general.general-report-pdf', [
            'reportData' => $reportData
        ]);
    }
}