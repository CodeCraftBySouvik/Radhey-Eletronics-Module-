<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\StockLog;

class StockLedgerExport implements FromCollection, WithHeadings, WithMapping
{
    protected $from_date;
    protected $to_date;
    protected $product_ids;

    public function __construct($from_date, $to_date, $product_ids = [])
    {
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->product_ids = $product_ids;
    }

    public function collection()
    {
        $data = collect();

        if (!empty($this->product_ids)) {
            $data = StockLog::whereIn('product_id', $this->product_ids)
                ->whereBetween('entry_date', [$this->from_date, $this->to_date])
                ->orderBy('product_id')
                ->orderBy('entry_date')
                ->orderBy('created_at')
                ->get();
        }

        return $data;
    }

    public function headings(): array
    {
        return ['Date','Product','Purpose','Particular','Rate','In','Out'];
    }

    public function map($item): array
    {
        $purpose = $particular = "";
        $in_quantity = $out_quantity = '';

        if ($item->type == 'in') {
            $in_quantity = $item->quantity;
            $purpose = "GOODS RECEIVED";
            $particular = "GRN / " . $item->stock->grn_no;
        }

        if ($item->type == 'out') {
            if (!empty($item->packingslip)) {
                $particular = "PACKING SLIP / " . $item->packingslip->slipno;
            } else if (!empty($item->purchase_return)) {
                $particular = "PURCHASE RETURN / " . $item->purchase_return->order_no;
            }
            $out_quantity = $item->quantity;
            $purpose = "GOODS DISBURSED";
        }

        return [
            date('d/m/Y', strtotime($item->entry_date)),
            $item->product->name,
            $purpose,
            $particular,
            !empty($item->piece_price) ? 'XOF. '.number_format((float)$item->piece_price, 2, '.', '') : '',
            $in_quantity,
            $out_quantity,
        ];
    }
}
