<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminOrderExport implements FromCollection, ShouldAutoSize, withHeadings, WithStyles
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
            'ID',
            'Order Ref',
            'Delivery Type',
            'Buyer',
            'Merchant',
            'Order Amount(GBP)',
            'Platform Fee(GBP)',
            'Shipping Fee(GBP)',
            'Total Amount(GBP)',
            'Payment Status',
            'Order Date',
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
