<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomersExport implements FromCollection,ShouldAutoSize,withHeadings,WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $item;

    function __construct ($item) {
        $this->item = $item;
    }
    public function collection() {
        return $this->item;
    }


    public function headings():array{
        return[
            'ID',
            'Account Type',
            'Merchant Name',
            'Merchant Phone',
            'Merchant Email',
            'Store Name',
            'Store Category',
            'Store Description',
            'Store Approved',
            'Registration Date', 
            'Active Subscription',
            'Product Count'
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
