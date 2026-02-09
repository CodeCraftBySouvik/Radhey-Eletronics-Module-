<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Models\{
    Invoice, InvoiceProduct, PackingslipNew1, Packingslip,
    Ledger, Bill, WhatsAppInvoice
};

class InvoiceApiController extends Controller
{
    public function deliverNow($packingslip_id)
{
    DB::beginTransaction();

    try {
        // 1️⃣ Find Packing Slip
        $packingSlip = PackingslipNew1::with('order.orderProducts')->findOrFail($packingslip_id);

        // Prevent duplicate invoice
        if ($packingSlip->invoice_id) {
            return response()->json([
                'status' => false,
                'message' => 'Invoice already generated for this packing slip'
            ], 400);
        }

        // 2️⃣ Get order items
        $orderItems = $packingSlip->order->orderProducts; // relation: order->items
        $net_price = 0;
        
        // 3️⃣ Generate invoice number
        $invoice_no = genAutoIncreNoInv();

        // 4️⃣ Create Invoice (initial net_price = 0, will update later)
        $invoiceId = Invoice::create([
            'packingslip_id' => $packingslip_id,
            'invoice_no' => $invoice_no,
            'net_price' => 0, // will update later
            'required_payment_amount' => 0, // will update later
            'order_id' => $packingSlip->order_id,
            'store_id' => $packingSlip->store_id,
            'user_id' => $packingSlip->user_id,
            'is_gst' => $packingSlip->is_gst ?? 1,
            'store_address_outstation' => $packingSlip->store_address_outstation ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $packingSlip->user_id
        ])->id;

        // 5️⃣ WhatsApp Invoice (initial net_price = 0, will update later)
        WhatsAppInvoice::create([
            'packingslip_id' => $packingslip_id,
            'invoice_id' => $invoiceId,
            'invoice_no' => $invoice_no,
            'net_price' => 0,
            'required_payment_amount' => 0,
            'order_id' => $packingSlip->order_id,
            'store_id' => $packingSlip->store_id,
            'user_id' => $packingSlip->user_id,
            'is_gst' => $packingSlip->is_gst ?? 1,
            'store_address_outstation' => $packingSlip->store_address_outstation ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
            'created_by' => $packingSlip->user_id
        ]);

        // 6️⃣ Process each order item
        foreach ($orderItems as $item) {

            // Get product details
            $productDetails = getOrderProductDetails($packingSlip->order_id, $item->product_id);
            $single_product_price = $productDetails->piece_price;
            $product_qty = $productDetails->qty;

            // GST and HSN
            $hsn_code = getSingleAttributeTable('products', $item->product_id, 'hsn_code');
            $igst = getSingleAttributeTable('products', $item->product_id, 'igst');
            $cgst = getSingleAttributeTable('products', $item->product_id, 'cgst');
            $sgst = getSingleAttributeTable('products', $item->product_id, 'sgst');

            // Determine GST type
            if (!empty($packingSlip->store->address_outstation)) {
                $gst_val = $igst;
            } else {
                $gst_val = ($cgst + $sgst);
            }

            // Calculate net amount & GST for single product
            $gstCalculation = getGSTAmount($single_product_price, $igst);
            $single_pro_gst_amount = $gstCalculation['gst_amount'];
            $single_pro_net_amount = $gstCalculation['net_price'];

            $total_price = $single_pro_net_amount * $item->pcs;

            // Calculate final GST price
            $gst_calculation = getPercentageVal($gst_val, $total_price);
            $product_gst_price = $total_price + $gst_calculation;

            // Add to invoice net price
            $net_price += $product_gst_price;

            // Save Invoice Product
            InvoiceProduct::create([
                'invoice_id' => $invoiceId,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'pcs' => $item->pcs,
                'price' => $single_pro_net_amount,
                'single_product_price' => $single_product_price,
                'count_price' => $total_price,
                'total_price' => $product_gst_price,
                'is_store_address_outstation' => $packingSlip->store->address_outstation,
                'hsn_code' => $hsn_code,
                'igst' => $igst,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 7️⃣ Update Invoice & WhatsApp Invoice with final net_price
        Invoice::where('id', $invoiceId)->update([
            'net_price' => $net_price,
            'required_payment_amount' => $net_price
        ]);

        WhatsAppInvoice::where('invoice_id', $invoiceId)->update([
            'net_price' => $net_price,
            'required_payment_amount' => $net_price
        ]);

        // 8️⃣ Bill
        DB::table('bills')->insert([
            'store_id' => $packingSlip->store_id,
            'invoice_id' => $invoiceId,
            'transaction_id' => $invoice_no,
            'entry_date' => now()->toDateString(),
            'amount' => $net_price,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 9️⃣ Update Packing Slip
        $packingSlip->update([
            'invoice_id' => $invoiceId,
            'status' => 'Delivered'
        ]);

        // 🔟 Ledger
        Ledger::create([
            'user_type' => 'store',
            'store_id' => $packingSlip->store_id,
            'transaction_id' => $invoice_no,
            'transaction_amount' => $net_price,
            'is_debit' => 1,
            'bank_cash' => $packingSlip->is_gst ? 'bank' : 'cash',
            'is_gst' => $packingSlip->is_gst ?? 1,
            'entry_date' => now()->toDateString(),
            'purpose' => 'invoice',
            'purpose_description' => 'invoice raised of sales order for store',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Invoice generated & delivered successfully',
            'invoice_id' => $invoiceId,
            'invoice_no' => $invoice_no,
            'net_amount' => $net_price
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}


    



}
