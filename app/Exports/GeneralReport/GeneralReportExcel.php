<?php

namespace App\Exports\GeneralReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneralReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $reportData;

    public function __construct(array $reportData)
    {
        $this->reportData = $reportData;
    }

    public function view(): View
    {
        $reportData = $this->reportData;
        ;
        return view('comb_and_clippers_admin.exports.general.general-report-excel', [
            'reportData' => $reportData
        ]);

    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}