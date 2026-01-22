<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Supplier;
use App\Models\PurchaseReturnProduct;
use App\Models\PurchaseReturnBox;

class PurchaseReturnOrder extends Model
{
    protected $table = "purchase_return_orders";

    /**
     * Get the supplier that owns the PurchaseReturnOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    /**
     * Get all of the purchase_return_products for the PurchaseReturnOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchase_return_products(): HasMany
    {
        return $this->hasMany(PurchaseReturnProduct::class, 'return_id', 'id');
    }

    /**
     * Get all of the box for the PurchaseReturnOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function box(): HasMany
    {
        return $this->hasMany(PurchaseReturnBox::class, 'return_id', 'id');
    }
}
