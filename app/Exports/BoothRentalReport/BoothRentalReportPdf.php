<?php

namespace App\Exports\BoothRentalReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BoothRentalReportPdf implements FromView
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
        return view('comb-and-clippers-admin.exports.booth-rental.booth-rental-report-pdf', [
            'boothRentals' => $this->boothRentals,
            'totalRentals' => $this->totalRentals,
            'totalAmount' => $this->totalAmount,
        ]);
    }
}