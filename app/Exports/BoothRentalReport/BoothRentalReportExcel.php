<?php

namespace App\Exports\BoothRentalReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BoothRentalReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $boothRentals;
    protected $totalRentals;
    protected $totalAmount;

    public function __construct($boothRentals, $totalRentals, $totalAmount)
    {
        $this->boothRentals = $boothRentals;
        $this->totalRentals = $totalRentals;
        $this->totalAmount = $totalAmount;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.boothRental.booth-rental-report-excel', [
            'boothRentals' => $this->boothRentals,
            'totalRentals' => $this->totalRentals,
            'totalAmount' => $this->totalAmount,
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
            'F' => ['width' => 20],
            'G' => ['width' => 15],
            'H' => ['width' => 15],
        ];
    }
}