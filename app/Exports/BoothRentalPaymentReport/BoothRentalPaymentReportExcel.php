<?php

namespace App\Exports\BoothRentalPaymentReport;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BoothRentalPaymentReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $boothPayments;
    protected $totalRevenue;
    protected $pendingPayments;
    protected $overduePayments;
    protected $processingFees;

    public function __construct($boothPayments, $totalRevenue, $pendingPayments, $overduePayments, $processingFees)
    {
        $this->boothPayments = $boothPayments;
        $this->totalRevenue = $totalRevenue;
        $this->pendingPayments = $pendingPayments;
        $this->overduePayments = $overduePayments;
        $this->processingFees = $processingFees;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.boothRentalPayment.boothRentalPayment-report-excel', [
            'boothPayments' => $this->boothPayments,
            'totalRevenue' => $this->totalRevenue,
            'pendingPayments' => $this->pendingPayments,
            'overduePayments' => $this->overduePayments,
            'processingFees' => $this->processingFees,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
            'A' => ['width' => 5],             // Adjust column widths as needed
            'B' => ['width' => 25],
            'C' => ['width' => 20],
            'D' => ['width' => 15],
            'E' => ['width' => 15],
            'F' => ['width' => 18],
            'G' => ['width' => 18],
            'H' => ['width' => 12],
        ];
    }
}