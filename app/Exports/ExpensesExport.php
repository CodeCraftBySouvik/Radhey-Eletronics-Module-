<?php

namespace App\Exports;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpensesExport implements FromCollection, WithHeadings
{
    protected $search;
    protected $entry_date;

    public function __construct($search, $entry_date)
    {
        $this->search = $search;
        $this->entry_date = $entry_date;
    }

    public function collection()
    {
        $data = Payment::where('voucher_no', 'LIKE', 'EXPENSE%');

        if (!empty($this->search)) {
            $data->where(function ($q) {
                $q->where('voucher_no', 'LIKE', '%' . $this->search . '%')
                  ->orWhere('narration', 'LIKE', '%' . $this->search . '%')
                  ->orWhereHas('creator', function ($cr) {
                      $cr->where('name', 'LIKE', '%' . $this->search . '%');
                  });
            });
        }

        if (!empty($this->entry_date)) {
            $data->where('payment_date', $this->entry_date);
        }

        return $data->orderBy('payment_date', 'desc')->get()->map(function ($row) {
            $staff = DB::table('users')->where('id', $row->staff_id)->first();
            $creator = DB::table('users')->where('id', $row->created_by)->first();

            return [
                $staff?->name ?? '',
                $row->payment_for ?? '',
                $row->voucher_no ?? '',
                $row->payment_date ?? '',
                $row->amount ?? '',
                $row->bank_cash ?? '',
                $row->payment_mode ?? '',
                $row->bank_name ?? '',
                $row->chq_utr_no ?? '',
                $row->narration ?? '',
                $creator?->name ?? '',
                $row->created_at ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Staff',
            'Payment For',
            'Voucher No',
            'Payment Date',
            'Amount',
            'Payment Type',
            'Payment Mode',
            'Bank Name',
            'Cheque / UTR No',
            'Narration',
            'Created By',
            'Created At',
        ];
    }
}
