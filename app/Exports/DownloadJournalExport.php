<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DownloadJournalExport implements FromCollection, WithHeadings, WithMapping
{
    protected $from_date;
    protected $to_date;
    protected $bank_cash;

    protected $net_value = 0;

    public function __construct($from_date, $to_date, $bank_cash)
    {
        $this->from_date = $from_date;
        $this->to_date   = $to_date;
        $this->bank_cash = $bank_cash;
    }

    public function collection(): Collection
    {
        $data = DB::table('journal AS l')
            ->select(
                'l.*',
                'p.voucher_no',
                'p.payment_in',
                'p.amount AS payment_amount',
                'p.payment_mode',
                'p.chq_utr_no',
                'p.narration'
            )
            ->leftJoin('payment AS p', 'p.id', 'l.payment_id')
            ->orderBy('l.entry_date', 'asc')
            ->orderBy('l.updated_at', 'asc');

        if (!empty($this->from_date) && !empty($this->to_date)) {
            $data->whereBetween('l.entry_date', [$this->from_date, $this->to_date]);
        }

        if (!empty($this->bank_cash)) {
            $data->where('l.bank_cash', $this->bank_cash);
        }

        return $data->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Transaction Id / Voucher No',
            'Purpose',
            'Debit',
            'Credit',
            'Closing',
        ];
    }

    public function map($item): array
    {
        $creditAmt = $debitAmt = '';

        if ($item->is_credit == 1) {
            $creditAmt = $item->transaction_amount;
            $this->net_value += $item->transaction_amount;
        }

        if ($item->is_debit == 1) {
            $debitAmt = $item->transaction_amount;
            $this->net_value -= $item->transaction_amount;
        }

        $show_payment_mode = !empty($item->bank_cash)
            ? '( ' . ucwords($item->bank_cash) . ' )'
            : '';

        return [
            date('d/m/Y', strtotime($item->entry_date)),
            $item->purpose_id,
            ucwords(str_replace('_', ' ', $item->purpose)) . ' ' . $show_payment_mode,
            replaceMinusSign($debitAmt),
            $creditAmt,
            replaceMinusSign($this->net_value) . ' ' . getCrDr($this->net_value),
        ];
    }
}
