<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    
    protected $table = "payment";

    /**
     * Get the store that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store::class, 'store_id', 'id');
    }

    /**
     * Get the staff that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'staff_id', 'id');
    }

    /**
     * Get the partner that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'admin_id', 'id');
    }

    /**
     * Get the supplier that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id', 'id');
    }

    /**
     * Get the expense that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expense(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Expense::class, 'expense_id', 'id');
    }

    /**
     * Get the created_by that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'created_by', 'id');
    }

    /**
     * Get the updated_by that owns the Payment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'updated_by', 'id');
    }

    public function approvedBy():BelongsTo
    {
         return $this->belongsTo(\App\User::class, 'approved_by', 'id');
    }

    public function getUserTypeAttribute()
{
    if ($this->staff_id) return 'Staff';
    if ($this->store_id) return 'Store';
    if ($this->supplier_id) return 'Supplier';
    if ($this->admin_id) return 'Admin';
    return 'Miscellaneous';
}

}
