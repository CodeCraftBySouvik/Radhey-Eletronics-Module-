<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesReportExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Order No / Invoice No',
            'Store',
            'Amount',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data as $item) {
            $rows[] = [
                $item['date'],
                $item['order_no'] . ' / ' . $item['invoice_no'],
                $item['store'],
                $item['amount'],
            ];
        }

        return $rows;
    }
}
