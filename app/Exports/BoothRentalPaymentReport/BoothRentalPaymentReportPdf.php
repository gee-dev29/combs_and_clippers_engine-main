<?php

namespace App\Exports\BoothRentalPaymentReport;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BoothRentalPaymentReportPdf implements FromView
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
        return view('comb_and_clippers_admin.exports.boothRentalPayment.boothRentalPayment-report-pdf', [
            'boothPayments' => $this->boothPayments,
            'totalRevenue' => $this->totalRevenue,
            'pendingPayments' => $this->pendingPayments,
            'overduePayments' => $this->overduePayments,
            'processingFees' => $this->processingFees,
        ]);
    }
}