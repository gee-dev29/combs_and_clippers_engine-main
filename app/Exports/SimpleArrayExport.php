<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class SimpleArrayExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return new Collection([
            ['Name', 'Amount'], // Header row
            ['John Doe', 100],
            ['Jane Smith', 200],
            ['Alice Brown', 150],
        ]);
    }
}