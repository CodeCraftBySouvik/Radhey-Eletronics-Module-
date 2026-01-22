<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockReportExport implements FromArray, WithHeadings, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'Product',
            'Total No Of Cartons',
            'Total No Of Pieces',
            'Total Stock Amount',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->data as $item) {
            $rows[] = [
                $item['product'],
                $item['count_stock'],
                $item['count_pcs'],
                'XOF. ' . number_format((float) $item['stock_price'], 2, '.', ''),
            ];
        }

        return $rows;
    }
}
