<?php

namespace App\Exports\StoreReport;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class StoreReportPdf implements FromView
{
    protected $stores;

    public function __construct($stores)
    {
        $this->stores = $stores;
    }

    public function view(): View
    {
        return view('exports.store-report-pdf', [
            'stores' => $this->stores,
        ]);
    }
}