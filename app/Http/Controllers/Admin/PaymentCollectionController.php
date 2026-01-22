<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentCollection;
use App\Models\PaymentRevoke;
use App\Models\Store;
use App\Models\Order;
use App\Models\StaffCommision;
use App\Models\Ledger;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class PaymentCollectionController extends Controller
{
    public function index(Request $request)
    {
        $desg = Auth::user()->designation;
        $paginate = 20;
        $store_id = (isset($_GET['store_id']) && $_GET['store_id']!='')?$_GET['store_id']:'';
        $store_name = (isset($_GET['store_name']) && $_GET['store_name']!='')?$_GET['store_name']:'';
        $staff_id = (isset($_GET['staff_id']) && $_GET['staff_id']!='')?$_GET['staff_id']:'';

        $data = PaymentCollection::with('stores')->with('users')
                ->when($store_id!='', function($query) use ($store_id){
                    $query->where('store_id', '=', $store_id);
                })
                ->when($staff_id!='', function($query) use ($staff_id){
                    $query->where('user_id', '=', $staff_id);
                })
                ->when($desg != NULL, function($query) use($desg){
                    $query->where('payment_type','!=','cash');
                })
                ->orderBy('cheque_date','desc')->paginate($paginate);
       
        $data = $data->appends(['store_id'=>$store_id,'staff_id'=>$staff_id,'page'=>$request->page]);
        $users = User::whereIn('designation',[1])->orWhere('type',1)->where('status',1)->get();
        $stores = Store::orderBy('bussiness_name')->get();
        $total = PaymentCollection::with('stores')->with('users')
                ->when($store_id!='', function($query) use ($store_id){
                    $query->where('store_id', '=', $store_id);
                })
                ->when($staff_id!='', function($query) use ($staff_id){
                    $query->where('user_id', '=', $staff_id);
                })
                ->when($desg != NULL, function($query) use($desg){
                    $query->where('payment_type','!=','cash');
                })
                ->count();
        
        return view('admin.paymentcollection.index', compact('data','store_name','store_id','users','stores','total','paginate'));
    }

    public function approve($id){
        $data = PaymentCollection::find($id);

        $is_approve = $data->is_approve;

        if ($is_approve==0) {
            

            $store_id = $data->store_id;

            $collected_amount = $data->collection_amount;
            $amount_redeemed = $collected_amount;

            //$orders = Order::where('store_id','=',$store_id)->where('final_amount','!=','paid_amount')->get();

            $orders = DB::select("select * from orders where store_id=$store_id and final_amount!=paid_amount");

            foreach ($orders as $order) {
                $order_id = $order->id;
                // code...
                $pending_amount = $order->final_amount - $order->paid_amount;

                if ($amount_redeemed>0 and $pending_amount>0) {
                    if ($pending_amount>$amount_redeemed) {
                        $result = DB::select("update orders set paid_amount=paid_amount+$amount_redeemed where id='$order_id'");

                        $amount_redeemed = $amount_redeemed - $amount_redeemed;
                    }else{
                        $result = DB::select("update orders set paid_amount=paid_amount+$pending_amount where id='$order_id'");

                        $result = DB::select("update orders set is_paid=1 where id='$order_id'");

                        $amount_redeemed = $amount_redeemed - $pending_amount;
                    }
                    // code...
                }



            }
            // echo "<pre>";
            // print_r($orders);
            // die();
            $data->is_approve = 1;
            $data->save();
        }
        

        return redirect()->route('admin.paymentcollection.index');
    }

    public function remove($id)
    {
        # removed not approved paymentc collection...
        PaymentCollection::where('id',$id)->delete();
        Session::flash('message', 'Removed successfully'); 
        return redirect()->route('admin.paymentcollection.index'); 
    }

    public function revoke($id)
    {
        # revoke payment...
        // dd($id);
        $payment_collections = PaymentCollection::find($id);
        $store_id = $payment_collections->store_id;
        $vouchar_no = $payment_collections->vouchar_no;
        $collection_amount = $payment_collections->collection_amount;
        $payment_id = $payment_collections->payment_id;
        
        $paymentRevoke = array(
            'store_id' => $store_id,
            'done_by' => Auth::user()->id,
            'vouchar_no' => $vouchar_no,
            'collection_amount' => $collection_amount,
            'paymentcollection_data_json' => json_encode($payment_collections),
            'created_at'=>date('Y-m-d H:i:s')
        );
        PaymentRevoke::insert($paymentRevoke);
        // dd($payment_collections);
        $collection_data = $paymentIds = array();
        $other_payment_collections = PaymentCollection::where('store_id',$store_id)->where('id','!=',$id)->orderBy('cheque_date','asc')->get();
        foreach($other_payment_collections as $collections){
            $paymentIds[] = $collections->payment_id;
            $collection_data[] = array(
                'id' => $collections->id,
                'store_id' => $collections->store_id,
                'user_id' => $collections->user_id,
                'admin_id' => $collections->admin_id,
                'payment_id' => $collections->payment_id,
                'collection_amount' => $collections->collection_amount,
                'cheque_date' => $collections->cheque_date,
                'vouchar_no' => $collections->vouchar_no,
                'payment_type' => $collections->payment_type,
                'created_at' => date('Y-m-d H:i:s', strtotime($collections->created_at))
            );
        }
        // dd($collection_data);

        $invoiceIds = array();
        $all_invoices = Invoice::where('store_id',$store_id)->get();
        foreach($all_invoices as $invoice){
            $invoiceIds[] = $invoice->id;
            # Revert Invoice Required Amount to Net Amount and All Payment Status
            Invoice::where('id',$invoice->id)->update([
                'required_payment_amount' => $invoice->net_price,
                'payment_status' => 0,
                'is_paid' => 0
            ]);

        }

        $commisionIds = array();
        $staff_commisions = StaffCommision::whereIn('invoice_id',$invoiceIds)->get();
        foreach($staff_commisions as $comm){
            $commisionIds[] = $comm->id;
        }
        # Delete Staff Commision Ledger
        Ledger::whereIn('staff_commision_id',$commisionIds)->delete();
        # Delete Staff Commision
        StaffCommision::whereIn('id',$commisionIds)->delete();
        # Delete Invoice Payments
        InvoicePayment::whereIn('invoice_id',$invoiceIds)->delete();

        # Delete Ledger
        Ledger::where('payment_id',$payment_id)->delete();
        # Delete Journal
        Journal::where('payment_id',$payment_id)->delete();
        # Delete Payment
        Payment::where('id',$payment_id)->delete();

        $this->resetInvoicePayments($store_id,$collection_data);

        # Delete Payment Collection
        PaymentCollection::where('id',$id)->delete();

        Session::flash('message', 'Payment revoked successfully'); 
        return redirect()->route('admin.paymentcollection.index'); 
    }

    private function resetInvoicePayments($store_id,$collection_data)
    {
        foreach($collection_data as $payments){
            // echo 'vouchar_no:- '.$payments['vouchar_no'].'<br/>';
            // echo 'collection_amount:- '.$payments['collection_amount'].'<br/>';
            // echo 'created_at:- '.$payments['created_at'].'<br/>';
            // die;
            $payment_amount = $payments['collection_amount'];
            $payment_collection_id = $payments['id'];
            
            $check_invoice_payments = DB::table('invoice_payments')->where('voucher_no','=',$payments['vouchar_no'])->get()->toarray();

            if(empty($check_invoice_payments)){
                $amount_after_settlement = $payment_amount;
                /* Check store unpaid invoices */
                $invoice = Invoice::where('store_id',$store_id)->where('is_paid',0)->orderBy('id','asc')->get();
                // dd($invoice);
                $sum_inv_amount = 0;
                foreach($invoice as $inv){                
                    $amount = $inv->required_payment_amount;
                    $sum_inv_amount += $amount;
                    if($amount == $payment_amount){
                        // die('Full Covered');
                        Invoice::where('id',$inv->id)->update([
                            'required_payment_amount'=>'',
                            'payment_status' => 2,
                            'is_paid'=>1
                        ]);
                        InvoicePayment::insert([
                            'invoice_id' => $inv->id,
                            'payment_collection_id' => $payment_collection_id,
                            'invoice_no' => $inv->invoice_no,
                            'voucher_no' => $payments['vouchar_no'],
                            'invoice_amount' => $inv->net_price,
                            'vouchar_amount' => $payment_amount,
                            'paid_amount' => $amount,
                            'rest_amount' => '',
                            'created_at' => $payments['created_at'],
                            'updated_at' => $payments['created_at']
                        ]);
                        $amount_after_settlement = 0;
                    } else{
                        // die('Not Full Covered');
                        if($amount_after_settlement>$amount && $amount_after_settlement>0){
                            $amount_after_settlement=$amount_after_settlement-$amount;
                            // echo $amount.'<br/>';
                            // echo $inv->id.'<br/>';
                            // die('Some invoice full covered');
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>'',
                                'payment_status' => 2,
                                'is_paid'=>1
                            ]);    
                            InvoicePayment::insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $payments['vouchar_no'],
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount,
                                'rest_amount' => '',
                                'created_at' => $payments['created_at'],
                                'updated_at' => $payments['created_at']
                            ]);
                        }else if($amount_after_settlement<$amount && $amount_after_settlement>0){
                            // echo $amount.'<br/>';
                            // echo $inv->id.'<br/>';
                            // die('Some invoice half covered');
                            $rest_payment_amount = ($amount - $amount_after_settlement);
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>$rest_payment_amount,
                                'payment_status' => 1,
                                'is_paid'=>0
                            ]);
                            InvoicePayment::insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $payments['vouchar_no'],
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount_after_settlement,
                                'rest_amount' => $rest_payment_amount,
                                'created_at' => $payments['created_at'],
                                'updated_at' => $payments['created_at']
                            ]);    
                            $amount_after_settlement = 0;                                            
                        }else if($amount_after_settlement==0){
                            
                        }
                    }
                    
                }                
                
                ### For Now Invoice Staff Commission Is Off , Generating salesman payment commission through report section
                #####
                // $this->resetStaffCommisions($payments['vouchar_no'],$payments['created_at']);
            } else {
                
            }
                
            
        }
        
    }

    private function resetStaffCommisions($voucher_no,$created_at)
    {
        # Get dynamic percentage value from app settings
        $settings = DB::table('settings')->find(1);
        $staff_payment_incentive = $settings->staff_payment_incentive;        
        $order_collector_commission = $settings->order_collector_commission;
        $payment_collector_commission = $settings->payment_collector_commission;

        /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

        $payment = DB::table('payment')->where('voucher_no',$voucher_no)->first();
        $payment_collector_id = $payment->staff_id;
        $payment_date = $payment->payment_date;
        
        # get full done invoice payments

        $paid_invoices = DB::table('invoice_payments AS ip')->select('ip.*','invoice.order_id','orders.user_id')->leftJoin('invoice', 'invoice.id','ip.invoice_id')->leftJoin('orders', 'orders.id','invoice.order_id')->where('ip.rest_amount', 0)->where('ip.voucher_no',$voucher_no)->where('ip.is_commisionable', 0)->get()->toarray();

        if(!empty($paid_invoices)){
            foreach($paid_invoices as $inv){
                $order_creator_id = $inv->user_id;
                $paid_amount = $inv->paid_amount;
    
                $commission_amount = getPercentageVal($staff_payment_incentive,$paid_amount);
                $commission_amount = number_format((float)$commission_amount, 2, '.', '');
    
                // echo "commission_amount : ".$commission_amount."<br/>";
                $order_collector_commission_amount = getPercentageVal($order_collector_commission,$commission_amount);
                $order_collector_commission_amount = number_format((float)$order_collector_commission_amount, 2, '.', '');
    
                $payment_collector_commission_amount = getPercentageVal($payment_collector_commission,$commission_amount);
                $payment_collector_commission_amount = number_format((float)$payment_collector_commission_amount, 2, '.', '');
    
                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    
                /* Order creator commission entry */
                # staff_commision entry
                $order_creator_commision_id = StaffCommision::insertGetId([
                    'staff_id' => $order_creator_id,
                    'paid_as' => 'order_creator',
                    'vouchar_no' => $voucher_no,
                    'order_id' => $inv->order_id,
                    'invoice_id' => $inv->invoice_id,
                    'invoice_payment_id' => $inv->id,
                    'commission_percentage_val' => $order_collector_commission,
                    'commission_amount' => $order_collector_commission_amount,
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);

                # ledger entry  
                $order_creator_ledger_transaction_no = time();
                Ledger::insert([
                    'user_type' => 'staff',
                    'staff_id' => $order_creator_id,
                    'staff_commision_id' => $order_creator_commision_id,
                    'transaction_id' => $order_creator_ledger_transaction_no,
                    'transaction_amount' => $order_collector_commission_amount,
                    'is_credit' => 1,
                    'entry_date' => $payment_date,
                    'purpose' => 'payment_collection_commission',
                    'purpose_description' => 'Sales order payment commission for order creator',
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
                
                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    
                /* Payment collector commission entry */
    
                $payment_collector_commision_id = StaffCommision::insertGetId([
                    'staff_id' => $payment_collector_id,
                    'paid_as' => 'payment_collector',
                    'vouchar_no' => $voucher_no,
                    'order_id' => $inv->order_id,
                    'invoice_id' => $inv->invoice_id,
                    'invoice_payment_id' => $inv->id,
                    'commission_percentage_val' => $payment_collector_commission,
                    'commission_amount' => $payment_collector_commission_amount,
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);

                # ledger entry                
                $payment_collector_ledger_transaction_no = time();
                Ledger::insert([
                    'user_type' => 'staff',
                    'staff_id' => $payment_collector_id,
                    'staff_commision_id' => $payment_collector_commision_id,
                    'transaction_id' => $payment_collector_ledger_transaction_no,
                    'transaction_amount' => $payment_collector_commission_amount,
                    'is_credit' => 1,
                    'entry_date' => $payment_date,
                    'purpose' => 'payment_collection_commission',
                    'purpose_description' => 'Sales order payment commission for payment collector',
                    'created_at' => $created_at,
                    'updated_at' => $created_at
                ]);
                    
                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    
                /* Make invice payment staff commissionable */
    
                InvoicePayment::where('id',$inv->id)->update([
                    'is_commisionable' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
    
                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    
            }
        }
    }
}
