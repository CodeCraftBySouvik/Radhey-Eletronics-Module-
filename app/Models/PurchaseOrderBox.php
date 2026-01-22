<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PurchaseOrder;
use App\Models\Product;

class PurchaseOrderBox extends Model
{
    

    protected $table = "purchase_order_boxes";

    /**
     * Get the purchase_orders that owns the PurchaseOrderBox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchase_orders(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    

    /**
     * Get the product that owns the PurchaseOrderBox
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
