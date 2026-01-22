<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    // protected $fillable = ['ip', 'user_id', 'fname', 'lname', 'email', 'mobile', 'alt_mobile', 'billing_address_id', 'billing_address', 'billing_landmark', 'billing_country', 'billing_state', 'billing_city', 'billing_pin', 'shipping_address_id', 'shipping_address', 'shipping_landmark', 'shipping_country', 'shipping_state', 'shipping_city', 'shipping_pin', 'amount', 'tax_amount', 'discount_amount', 'coupon_code_id', 'final_amount','paid_amount', 'gst_no', 'is_paid', 'txn_id'];

    public function orderProducts() {
        return $this->hasMany('App\Models\OrderProduct', 'order_id', 'id');
    }
    public function users() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function stores() {
        return $this->belongsTo('App\Models\Store', 'store_id', 'id');
    }
    /**
     * Get the packingslip associated with the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function packingslip(): HasOne
    {
        return $this->hasOne(\App\Models\PackingslipNew1::class, 'order_id', 'id');
    }

    /**
     * Get the invoice associated with the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function invoice(): HasOne
    {
        return $this->hasOne(\App\Models\Invoice::class, 'order_id', 'id');
    }

    /**
     * Get all of the pending_thresholds for the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pending_thresholds(): HasMany
    {
        return $this->hasMany(ThresholdRequest::class, 'hold_order_id', 'id')->where('is_approved', 0);
    }
    

    /**
     * Get all of the thresholds for the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function thresholds(): HasMany
    {
        return $this->hasMany(ThresholdRequest::class, 'hold_order_id', 'id');
    }
}
