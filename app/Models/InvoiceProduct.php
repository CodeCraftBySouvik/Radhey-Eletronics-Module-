<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Invoice;
use App\Models\Product;

class InvoiceProduct extends Model
{
    //
    protected $table = "invoice_products";
    protected $fillable = [
        'invoice_id', 'product_id', 'product_name', 'quantity', 'pcs', 'price', 'single_product_price', 'count_price', 'total_price', 'is_store_address_outstation', 'hsn_code', 'igst', 'cgst', 'sgst', 'created_at', 'updated_at'
    ];

    /**
     * Get the invoice that owns the InvoiceProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    /**
     * Get the product that owns the InvoiceProduct
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
