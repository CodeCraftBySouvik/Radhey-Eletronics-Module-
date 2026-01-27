<?php

namespace App\Exports;

use App\Models\InvoiceProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesAnalysisExport implements FromCollection, WithHeadings, WithMapping
{
    protected $from_date;
    protected $to_date;
    protected $product_ids;
    protected $store_ids;

    protected $rowNo = 1;

    public function __construct($from_date, $to_date, $product_ids = [], $store_ids = [])
    {
        $this->from_date   = $from_date;
        $this->to_date     = $to_date;
        $this->product_ids = $product_ids;
        $this->store_ids   = $store_ids;
    }

    public function collection(): Collection
    {
        $data = collect();

        if (!empty($this->product_ids)) {

            $data = InvoiceProduct::whereIn('product_id', $this->product_ids)
                ->whereHas('invoice', function ($invoice) {
                    $invoice->whereBetween(
                        DB::raw('DATE(created_at)'),
                        [$this->from_date, $this->to_date]
                    );
                });

            if (!empty($this->store_ids)) {
                $data->whereHas('invoice', function ($store) {
                    $store->whereIn('store_id', $this->store_ids);
                });
            }

            $data = $data->orderBy('product_id', 'asc')->get();
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            '#',
            'Date',
            'Product',
            'Store',
            'Order No / Invoice No',
            'Total Cartons',
            'Total Pieces',
            'Rate',
            'Total',
        ];
    }

    public function map($item): array
    {
        $order_no = $item->invoice->order->order_no;
        $invoice_no = $item->invoice->invoice_no;

        return [
            $this->rowNo++,
            date('d/m/Y', strtotime($item->created_at)),
            $item->product->name,
            $item->invoice->store->bussiness_name,
            $order_no . ' / ' . $invoice_no,
            $item->quantity . ' ctns',
            $item->pcs . ' pcs',
            'XOF. ' . number_format((float)$item->single_product_price, 2, '.', ''),
            'XOF. ' . number_format((float)$item->total_price, 2, '.', ''),
        ];
    }
}
