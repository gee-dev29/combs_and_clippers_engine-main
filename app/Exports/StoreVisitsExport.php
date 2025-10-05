<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StoreVisitsExport implements FromCollection, ShouldAutoSize, withHeadings, WithStyles
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $item;

    function __construct($item)
    {
        $this->item = $item;
    }
    public function collection()
    {
        return $this->item;
    }


    public function headings(): array
    {
        return [
            'Date',
            'Store Name',
            'Total Visits'
        ];
    }
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
