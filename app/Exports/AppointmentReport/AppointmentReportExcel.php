<?php

namespace App\Exports\AppointmentReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AppointmentReportExcel implements FromView, ShouldAutoSize, WithStyles
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
        $appointments = $this->appointments;
        $totalAppointments = $this->totalAppointments;
        $totalAmount = $this->totalAmount;
        $totalTips = $this->totalTips;
        $totalProcessingFees = $this->totalProcessingFees;
        return view('comb_and_clippers_admin.exports.appointment.appointment-report-excel', [
            'appointments' => $appointments,
            'totalAppointments' => $totalAppointments,
            'totalAmount' => $totalAmount,
            'totalTips' => $totalTips,
            'totalProcessingFees' => $totalProcessingFees,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
            'A' => ['width' => 5],             // Adjust column widths as needed
            'B' => ['width' => 20],
            'C' => ['width' => 20],
            'D' => ['width' => 25],
            'E' => ['width' => 20],
            'F' => ['width' => 15],
            'G' => ['width' => 15],
            'H' => ['width' => 15],
        ];
    }
}