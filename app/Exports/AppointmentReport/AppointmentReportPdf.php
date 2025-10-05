<?php

namespace App\Exports\AppointmentReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class AppointmentReportPdf implements FromView
{
    protected $appointments;
    protected $totalAppointments;
    protected $totalAmount;
    protected $totalTips;
    protected $totalProcessingFees;

    public function __construct($appointments, $totalAppointments, $totalAmount, $totalTips, $totalProcessingFees)
    {
        $this->appointments = $appointments;
        $this->totalAppointments = $totalAppointments;
        $this->totalAmount = $totalAmount;
        $this->totalTips = $totalTips;
        $this->totalProcessingFees = $totalProcessingFees;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.appointment.appointment-report-pdf', [
            'appointments' => $this->appointments,
            'totalAppointments' => $this->totalAppointments,
            'totalAmount' => $this->totalAmount,
            'totalTips' => $this->totalTips,
            'totalProcessingFees' => $this->totalProcessingFees,
        ]);
    }
}