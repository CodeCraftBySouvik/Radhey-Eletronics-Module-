<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StoreDuePaymentsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Store',
            'Due Remaining',
            'Unpaid Amount',
        ];
    }

    public function map($item): array
    {
        $amount = $item['amount'];

        return [
            $item['store_name'],
            $item['due_days'] . ' days',
            replaceMinusSign($amount) . ' ' . getCrDr($amount),
        ];
    }
}
