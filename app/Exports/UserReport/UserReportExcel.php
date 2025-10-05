<?php

namespace App\Exports\UserReport;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserReportExcel implements FromView, ShouldAutoSize, WithStyles
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function view(): View
    {
        return view('comb_and_clippers_admin.exports.user.user-report-excel', [
            'users' => $this->users,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Header row bold
            'A' => ['width' => 5],             // Adjust column widths as needed
            'B' => ['width' => 25],
            'C' => ['width' => 30],
            'D' => ['width' => 15],
            'E' => ['width' => 15],
            'F' => ['width' => 20],
            'G' => ['width' => 20],
            'H' => ['width' => 25],
            'I' => ['width' => 25],
            'J' => ['width' => 20],
            'K' => ['width' => 10],
            'L' => ['width' => 25],
            'M' => ['width' => 25],
        ];
    }
}