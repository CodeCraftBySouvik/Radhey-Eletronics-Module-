<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockProduct;

class StockBox extends Model
{
    protected $table = "stock_boxes";

    /**
     * Get the product that owns the StockBox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /**
     * Get the stock_product that owns the StockBox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stock_product(): BelongsTo
    {
        return $this->belongsTo(StockProduct::class, 'stock_id', 'id');
    }

    /**
     * Get the stock that owns the StockBox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id', 'id');
    }

    /**
     * Get the purchase_return that owns the StockBox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase_return(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturnOrder::class, 'purchase_return_id', 'id');
    }
}
