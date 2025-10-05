<?php

namespace App\Exports\UserReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UserReportPdf implements FromView
{
    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    public function view(): View
    {
        return view('exports.user-report-pdf', [
            'users' => $this->users,
        ]);
    }
}