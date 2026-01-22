<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;
use App\Models\PurchaseReturnOrder;

class StockLog extends Model
{
    //
    protected $table = "stock_logs";

    /**
     * Get the stock that owns the StockLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Stock::class, 'stock_id', 'id');
    }

    /**
     * Get the packingslip that owns the StockLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function packingslip(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PackingslipNew1::class, 'packingslip_id', 'id');
    }

    /**
     * Get the product that owns the StockLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the purchase_return that owns the StockLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase_return(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturnOrder::class, 'purchase_return_id', 'id');
    }
}
