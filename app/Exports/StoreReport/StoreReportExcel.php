<?php

namespace App\Exports\StoreReport;

use App\Models\Store;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StoreReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $stores;

    public function __construct($stores)
    {
        $this->stores = $stores;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.store.store-report-excel', [
            'stores' => $this->stores,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
            'A' => ['width' => 5],             // Adjust column widths as needed
            'B' => ['width' => 30],
            'C' => ['width' => 25],
            'D' => ['width' => 20],
            'E' => ['width' => 15],
            'F' => ['width' => 15],
            'G' => ['width' => 20],
            'H' => ['width' => 10],
            'I' => ['width' => 10],
            'J' => ['width' => 20],
        ];
    }
}