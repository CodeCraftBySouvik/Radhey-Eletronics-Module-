<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PurchaseOrderBox;

class PurchaseOrder extends Model
{
    
    /**
     * Get the supplier that owns the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id', 'id');
    }

    /**
     * Get all of the comments for the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function count_archived()
    {
        return $this->hasMany(\App\Models\PurchaseOrderBox::class, 'purchase_order_id', 'id')->where('is_archived', 1);
        
    }

    /**
     * Get all of the stock for the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stock(): HasMany
    {
        return $this->hasMany(\App\Models\Stock::class, 'purchase_order_id', 'id');
    }

    /**
     * Get all of the purchase_order_products for the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchase_order_products(): HasMany
    {
        return $this->hasMany(\App\Models\PurchaseOrderProduct::class, 'purchase_order_id', 'id');
    }

    /**
     * Get all of the purchase_order_box for the PurchaseOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchase_order_box(): HasMany
    {
        return $this->hasMany(\App\Models\PurchaseOrderBox::class, 'purchase_order_id', 'id');
    }

   
}
