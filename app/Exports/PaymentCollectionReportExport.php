<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentCollectionReportExport implements FromCollection, WithHeadings
{
    protected $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Voucher No',
            'Store',
            'Amount',
        ];
    }
}
