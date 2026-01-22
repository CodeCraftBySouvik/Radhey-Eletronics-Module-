<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use DateTime;
use App\Models\Store;
use App\Models\Stock;
use App\Models\StockBox;
use App\Models\StockProduct;
use App\Models\PurchaseOrderBox;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Payment;
use App\Models\PaymentUpdate;
use App\Models\Invoice;
use App\Models\InvoiceRevoke;
use App\Models\InvoiceProduct;
use App\Models\InvoicePayment;
use App\Models\Packingslip;
use App\Models\Ledger;
use App\Models\Journal;
use App\Models\PaymentCollection;
use App\Models\StaffCommision;
use App\Models\ServiceSlip;
use App\Models\StockBarcodeUpload;
use App\Models\Product;
use App\Models\StockAudit;
use App\Models\StockAuditFinal;
use App\Models\PurchaseOrderProduct;
use App\Models\StockLog;
use App\Models\PackingslipNew1;


use Barryvdh\DomPDF\Facade\Pdf;

class TestController extends Controller
{
    //
    public function save(Request $request)
    {
        // die("Save");
        # partner expense voucher
        $voucher_no = 'PRTEXP'.time();
        echo $voucher_no;
        $data = DB::table('payment')->whereIn('admin_id',[1,2])->where('payment_for', 'credit')->where('voucher_no', 'LIKE','%EXPENSE%')->get();

        foreach($data as $item){
            $rest = substr($item->voucher_no,7);
            // dd($rest);
            $new_voucher_no = 'PTREXP'.$rest;
            // dd($new_voucher_no);
            $item->new_voucher_no = $new_voucher_no;
            $ledger = DB::table('ledger')->where('payment_id',$item->id)->first();
            $item->transaction_id = $ledger->transaction_id;
            // DB::table('payment')->where('id',$item->id)->update([
            //     'voucher_no'=>$new_voucher_no
            // ]);
            // DB::table('ledger')->where('id',$ledger->id)->update([
            //     'transaction_id'=>$new_voucher_no,
            //     'purpose' => 'partner_expense'
            // ]);
        }
        dd($data);
        
        // $voucher_no = 'PTREXP20230414153044';
    }

    public function generateStaffSalary(Request $request)
    {
        # test staff salary...
        $entry_date = !empty($request->entry_date)?$request->entry_date:'';
        if(!empty($entry_date)){
            $staff = DB::table('users')->select('id','name','monthly_salary','daily_salary')->where('type', 2)->where('status', 1)->get();

            if(!empty($staff)){
                foreach($staff as $user){
                    /* Salary Generation */
                    
                    $checkExistSalaryDayLedger = DB::table('ledger')->where('staff_id',$user->id)->where('purpose','salary')->where('entry_date', $entry_date)->first();
                    if(empty($checkExistSalaryDayLedger)){
                        $transaction_id = "SAL".$user->id."".date('Ymd').time();
                        $user->salary_id = $transaction_id;
                        Ledger::insert([
                            'user_type' => 'staff',
                            'staff_id' => $user->id,
                            'transaction_id' => $transaction_id,
                            'transaction_amount' => $user->daily_salary,
                            'is_credit' => 1,
                            'entry_date' => $entry_date,
                            'purpose' => 'salary',
                            'purpose_description' => "Staff Daily Salary",
                            'created_at'=>date('Y-m-d H:i:s')
                        ]);
                    }           
                }
                echo "Salary created for ".count($staff)." staffs for ".$entry_date." ";
            } else {
                echo "No staff found";
            }
        } else {
            echo "Please add query param <strong>entry_date</strong> ";
        }
        

    }

    public function invoicePayments(Request $request)
    {
        # code...

        $voucher_no = !empty($request->voucher_no)?$request->voucher_no:'PAYRECEIPT1678805084';
        // $payment_amount = !empty($request->payment_amount)?$request->payment_amount:'16500.00';
        // $store_id = !empty($request->store_id)?$request->store_id:'51';
        // $payment_collection_id = !empty($request->payment_collection_id)?$request->payment_collection_id:'';

        $payment_collection = PaymentCollection::where('vouchar_no',$voucher_no)->first();
        $payment_collection_id = $payment_collection->id;
        $payment_amount = $payment_collection->collection_amount;
        $store_id = $payment_collection->store_id;
        // dd($store_id);

        // echo $voucher_no; die;
        // dd($request->all());
        // die;

        if((!empty($voucher_no)) || (!empty($payment_amount)) || (!empty($store_id)) || (!empty($payment_collection_id))){
            // echo $voucher_no; die;
            $check_invoice_payments = DB::table('invoice_payments')->where('voucher_no','=',$voucher_no)->get()->toarray();

            // dd($check_invoice_payments); die;

            if(empty($check_invoice_payments)){
                
                // die('No invoice payments found');
                $amount_after_settlement = $payment_amount;
                /* Check store unpaid invoices */
                $invoice = DB::table('invoice')->where('store_id',$store_id)->where('is_paid',0)->orderBy('id','asc')->get();

                // dd($invoice); 
                // die;
    
                $sum_inv_amount = 0;
                foreach($invoice as $inv){
                    
                    $amount = $inv->required_payment_amount;
                    
                    $sum_inv_amount += $amount;
                    $amount = number_format((float)$amount, 2, '.', '');
                    $amount_after_settlement = number_format((float)$amount_after_settlement, 2, '.', '');
                    $sum_inv_amount = number_format((float)$sum_inv_amount, 2, '.', '');
                    echo 'amount_after_settlement:- '.$amount_after_settlement.'<br/>';
                    echo 'amount:- '.$amount.'<br/>';
                    // echo 'required_payment_amount:- '.$inv->required_payment_amount.'<br/>';
                    echo 'sum_inv_amount:- '.$sum_inv_amount.'<br/>';
                    // die;
                    if($amount == $payment_amount){
                        // dd('Exact same');
                        DB::table('invoice')->where('id',$inv->id)->update([
                            'required_payment_amount'=>'',
                            'payment_status' => 2,
                            'is_paid'=>1
                        ]);
    
                        DB::table('invoice_payments')->insert([
                            'invoice_id' => $inv->id,
                            'payment_collection_id' => $payment_collection_id,
                            'invoice_no' => $inv->invoice_no,
                            'voucher_no' => $voucher_no,
                            'invoice_amount' => $inv->net_price,
                            'vouchar_amount' => $payment_amount,
                            'paid_amount' => $amount,
                            'rest_amount' => '',
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                        $amount_after_settlement = 0;
                    } else {
                        if($amount_after_settlement>$amount && $amount_after_settlement>0){
                            $amount_after_settlement=$amount_after_settlement-$amount;
                            
                            // dd('Hi');
                            
                            DB::table('invoice')->where('id',$inv->id)->update([
                                'required_payment_amount'=>'',
                                'payment_status' => 2,
                                'is_paid'=>1
                            ]);
        
                            DB::table('invoice_payments')->insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $voucher_no,
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount,
                                'rest_amount' => '',
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        }else if($amount_after_settlement<$amount && $amount_after_settlement>0){
                            
                            $rest_payment_amount = ($amount - $amount_after_settlement);
                            
                            DB::table('invoice')->where('id',$inv->id)->update([
                                'required_payment_amount'=>$rest_payment_amount,
                                'payment_status' => 1,
                                'is_paid'=>0
                            ]);
                            DB::table('invoice_payments')->insert([
                                'invoice_id' => $inv->id,
                                'payment_collection_id' => $payment_collection_id,
                                'invoice_no' => $inv->invoice_no,
                                'voucher_no' => $voucher_no,
                                'invoice_amount' => $inv->net_price,
                                'vouchar_amount' => $payment_amount,
                                'paid_amount' => $amount_after_settlement,
                                'rest_amount' => $rest_payment_amount,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
        
                            $amount_after_settlement = 0;
                                                
                        }else if($amount_after_settlement==0){
                            
                        }
                    }
                    
                }
                
                
            }else{
                
            }
        }else{
            echo 'Please add voucher_no , payment_amount , store_id and payment_collection_id as query parameter';
        }
    }

    public function staffCommission(Request $request)
    {
        # save unpaid invoice's payments
        // die('Hello');

        # Get dynamic percentage value from app settings
        $settings = DB::table('settings')->find(1);
        $staff_payment_incentive = $settings->staff_payment_incentive;        
        $order_collector_commission = $settings->order_collector_commission;
        $payment_collector_commission = $settings->payment_collector_commission;

        /* +++++++++++++++++++++++++++++++++++++++++++++++++++++++ */

        $voucher_no = !empty($request->voucher_no)?$request->voucher_no:'';
        if(!empty($voucher_no)){
            $payment = DB::table('payment')->where('voucher_no',$voucher_no)->first();
            $payment_collector_id = $payment->staff_id;
            $payment_date = $payment->payment_date;
            // echo "payment_collector_id : ".$payment_collector_id."<br/>";
            // die;
            $paid_invoices = DB::table('invoice_payments AS ip')->select('ip.*','invoice.order_id','orders.user_id')->leftJoin('invoice', 'invoice.id','ip.invoice_id')->leftJoin('orders', 'orders.id','invoice.order_id')->where('ip.rest_amount', 0)->where('ip.voucher_no',$voucher_no)->where('ip.is_commisionable', 0)->get()->toarray();

            // dd($paid_invoices);

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


                // echo "order_collector_commission_amount : ".$order_collector_commission_amount."<br/>";
                // echo "payment_collector_commission_amount : ".$payment_collector_commission_amount."<br/>";

                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */

                /* Order creator commission entry */
                # staff_commision entry
                $order_creator_commision_id = DB::table('staff_commision')->insertGetId([
                    'staff_id' => $order_creator_id,
                    'paid_as' => 'order_creator',
                    'vouchar_no' => $voucher_no,
                    'order_id' => $inv->order_id,
                    'invoice_id' => $inv->invoice_id,
                    'invoice_payment_id' => $inv->id,
                    'commission_percentage_val' => $order_collector_commission,
                    'commission_amount' => $order_collector_commission_amount,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
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
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */
    
                /* Payment collector commission entry */
    
                $payment_collector_commision_id = DB::table('staff_commision')->insertGetId([
                    'staff_id' => $payment_collector_id,
                    'paid_as' => 'payment_collector',
                    'vouchar_no' => $voucher_no,
                    'order_id' => $inv->order_id,
                    'invoice_id' => $inv->invoice_id,
                    'invoice_payment_id' => $inv->id,
                    'commission_percentage_val' => $payment_collector_commission,
                    'commission_amount' => $payment_collector_commission_amount,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
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
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                

                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */

                /* Make invice payment staff commissionable */

                DB::table('invoice_payments')->where('id',$inv->id)->update([
                    'is_commisionable' => 1,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */

            }
            // dd($paid_invoices);
        }else{
            echo 'Please add voucher_no in query param';
        }
        
    }

    public function saveStoreVisitLedger(Request $request)
    {
        // $bills = DB::table('bills')->select('bills.*','stores.bussiness_name','stores.city_id')->leftJoin('stores', 'stores.id','bills.store_id')->orderBy('entry_date')->get();
        // foreach($bills as $bill){
        //     $isBillCommissionEligible = 0;
        //     $invoiceOld = date_diff(
        //         date_create($bill->entry_date),  
        //         date_create(date('Y-m-d'))
        //     )->format('%a');

        //     if($invoiceOld <= 60){
        //         $isBillCommissionEligible = 1;
        //     }
        //     $bill->invoiceOld = $invoiceOld." days";
        //     $bill->isBillCommissionEligible = $isBillCommissionEligible;
        // }
        // echo '<pre>'; print_r($bills); die;

        // $invoice_payments = DB::table('invoice_payments AS ip')->select('ip.invoice_id','ip.payment_collection_id','ip.invoice_amount','ip.vouchar_amount','ip.paid_amount','ip.rest_amount','ip.voucher_no','ip.invoice_no','i.store_id','pc.cheque_date','pc.user_id','pc.payment_id','pc.created_at AS payment_created_at','s.city_id')->selectRaw("DATE_FORMAT(i.created_at,'%Y-%m-%d') AS invoice_date")->leftJoin('invoice AS i','i.id','ip.invoice_id')->leftJoin('payment_collections AS pc','pc.id','ip.payment_collection_id')->leftJoin('stores AS s','s.id','i.store_id')->get();

        // foreach($invoice_payments as $ip){
        //     $isBillCommissionEligible = 0;
        //     $invoiceOld = date_diff(
        //         date_create($ip->invoice_date),  
        //         date_create($ip->cheque_date)
        //     )->format('%a');

        //     if($invoiceOld <= 60){
        //         $isBillCommissionEligible = 1;

        //         $year_val = date('Y', strtotime($ip->cheque_date));
        //         $month_val = date('m', strtotime($ip->cheque_date));

        //         $eligibleArr = array(
        //             'user_id' => $ip->user_id,
        //             'store_id' => $ip->store_id,
        //             'invoice_id' => $ip->invoice_id,
        //             'payment_id' => $ip->payment_id,
        //             'collect_within_days' => $invoiceOld,
        //             'invoice_paid_amount' => $ip->paid_amount,
        //             'city_id' => $ip->city_id,
        //             'year_val' => $year_val,
        //             'month_val' => $month_val,
        //             'created_at' => $ip->payment_created_at,
        //             'updated_at' => $ip->payment_created_at
        //         );
        //         // DB::table('staff_collection_commission_eligibility')->insert($eligibleArr);
        //     }
        //     $ip->invoiceOld = $invoiceOld." days";
        //     $ip->isBillCommissionEligible = $isBillCommissionEligible;
        
        // }

        die;

        $month_val = !empty($request->month_val)?$request->month_val:'09';
        $salesman = DB::table('users')->select('id','name','mobile','monthly_collection_target_value','targeted_collection_amount_commission')->where('designation', 1)->where('status', 1)->get()->toArray();

        $collection_cities = DB::table('staff_collection_commission_eligibility')->where('month_val', $month_val);
        $collection_cities = $collection_cities->groupBy('city_id')->whereNotNull('city_id')->pluck('city_id')->toArray();
        $collection_cities = json_encode($collection_cities);

        foreach($salesman as $user){
            $user_cities = DB::table('user_cities')->where('user_id',$user->id)->pluck('city_id')->toArray();
            $user->user_cities = json_encode($user_cities);
            $monthly_collection_target_value = $user->monthly_collection_target_value;
            $targeted_collection_amount_commission = $user->targeted_collection_amount_commission;
            
            $collection_amount = DB::table('staff_collection_commission_eligibility')->where('month_val', $month_val);
            $collection_amount = $collection_amount->whereIn('city_id', $user_cities);
            $collection_amount = $collection_amount->sum('invoice_paid_amount');

            $isCommissionAble = 0;
            if($collection_amount >= $monthly_collection_target_value){
                $isCommissionAble = 1;
            }
            $user->isCommissionAble = $isCommissionAble;

            echo '<pre>'; echo 'Salesman:- '.$user->name;
            echo '<pre>'; echo $user->name.' City Collections:- '; print_r($collection_amount);
            
        }

        echo '<pre>'; echo 'Collection Cities:- '; print_r($collection_cities);
        echo '<pre>'; print_r($salesman);  
        die;
        
    }

    public function removeLedgerRecord(Request $request)
    {
        # delete record from ledger, payment & journal...

        $transaction_id = !empty($request->transaction_id) ? $request->transaction_id : '';

        if(!empty($transaction_id)){
            $ledger = DB::table('ledger')->where('transaction_id',$transaction_id)->first();
            
            if(!empty($ledger)){
                DB::table('ledger')->where('transaction_id',$transaction_id)->delete();
                DB::table('payment')->where('voucher_no',$transaction_id)->delete();
                echo "Delete records for Transaction Id:- ".$transaction_id;
            }
            $journal = DB::table('journal')->where('purpose_id',$transaction_id)->first();
            if(!empty($journal)){
                DB::table('journal')->where('purpose_id',$transaction_id)->delete();
                DB::table('payment')->where('voucher_no',$transaction_id)->delete();
                echo "Delete records for Transaction Id:- ".$transaction_id;
            }
        } else {
            die("Please add {transaction_id} as query param ");
        }
    }

    public function checkDistance(Request $request)
    {
        # code...
        $lat1 = !empty($request->lat1)?$request->lat1:'0.0';
        $long1 = !empty($request->long1)?$request->long1:'0.0';
        $lat2 = !empty($request->lat2)?$request->lat2:'22.5761045';
        $long2 = !empty($request->long2)?$request->long2:'88.4338093';

        
        $GetDrivingDistanceTest = GetDrivingDistanceTest($lat1,$long1,$lat2,$long2);

        dd($GetDrivingDistanceTest);
    }

    public function index(Request $request)
    {
        $order_prod = OrderProduct::select('id','order_id','product_id','qty','pcs')->whereHas('orders', function($orders){
            $orders->where('status', 4);
        })->get()->toArray();

        foreach($order_prod as $prod){
            // dd($prod['order_id']);
            $packingslip_prod = Packingslip::where('order_id',$prod['order_id'])->where('product_id',$prod['product_id'])->first();

            // dd($packingslip_prod);

            $packingslip_prod_qty = !empty($packingslip_prod)?$packingslip_prod->quantity:'';
            $packingslip_prod_pcs = !empty($packingslip_prod)?$packingslip_prod->pcs:'';

            
            if($packingslip_prod_pcs != $prod['pcs']){
                echo ('order_id:-'.$prod['order_id'].'   product_id:-'.$prod['product_id'].' qty:-'.$prod['qty'].' packingslip_prod_qty:-'.$packingslip_prod_qty.' packingslip_prod_pcs:-'.$packingslip_prod_pcs.' pcs:-'.$prod['pcs']);
                echo '<br/>';
            }

            $packingslip_id = $packingslip_prod->packingslip_id;
            $order_id = $prod['order_id'];

            $invoice_prod = InvoiceProduct::whereHas('invoice', function($invoice)use($packingslip_id,$order_id){
                $invoice->where('packingslip_id',$packingslip_id)->where('order_id',$order_id);
            })->where('product_id',$prod['product_id'])->first();

            $invoice_prod_qty = !empty($invoice_prod)?$invoice_prod->quantity:'';
            $invoice_prod_pcs = !empty($invoice_prod)?$invoice_prod->pcs:'';

            if($invoice_prod_pcs != $prod['pcs']){
                echo ('order_id:-'.$prod['order_id'].'   product_id:-'.$prod['product_id'].' qty:-'.$prod['qty'].' packingslip_prod_qty:-'.$packingslip_prod_qty.' packingslip_prod_pcs:-'.$packingslip_prod_pcs.' pcs:-'.$prod['pcs'].' invoice_prod_qty:-'.$invoice_prod_qty.' invoice_prod_pcs:-'.$invoice_prod_pcs);
                echo '<br/>';
            }

            // dd($invoice_prod);
        }

        // echo '<pre>'; print_r($order_prod);
        
    }

    public function downloadInvoicePDF($invoice_no='0000000022')
    {
        $invoice = Invoice::select('*')->with('order','store','user','products')->where('invoice_no',$invoice_no)->first();
        $invpdfname = $invoice_no."";
        if(!empty($invoice->is_gst)){
            $pdf = Pdf::loadView('admin.packingslip.invoice', compact('invoice'));
            return $pdf->download($invpdfname.'.pdf');
        } else {
            $pdf = Pdf::loadView('admin.packingslip.cashslip', compact('invoice'));
            return $pdf->download($invpdfname.'.pdf');
        }
    }

    public function resetInvoicePayments(Request $request)
    {
        # code...
        // echo 'Hi';
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $payment_collection_id = !empty($request->payment_collection_id)?$request->payment_collection_id:'';
        if(!empty($store_id)){
            $invoices = Invoice::where('store_id',$store_id)->get()->toArray();
            
            $invoiceIds = array();
            foreach($invoices as $invoice){
                $invoiceIds[] = $invoice['id'];
                
                Invoice::where('id',$invoice['id'])->update([
                    'required_payment_amount' => $invoice['net_price'],
                    'payment_status' => 0,
                    'is_paid' => 0
                ]);
            }
            $commisionIds = array();
            $staff_commisions = StaffCommision::whereIn('invoice_id',$invoiceIds)->get();
            foreach($staff_commisions as $comm){
                $commisionIds[] = $comm->id;
            }
            // dd($commisionIds);
            # Delete Staff Commision Ledger
            Ledger::whereIn('staff_commision_id',$commisionIds)->delete();
            # Delete Staff Commision
            StaffCommision::whereIn('id',$commisionIds)->delete();
            # Delete Invoice Payments
            InvoicePayment::whereIn('invoice_id',$invoiceIds)->delete();

            # Update Ledger
            // Ledger::where('payment_id',$params['payment_id'])->update([
            //     'transaction_amount' => $params['amount'],
            //     'updated_at' => date('Y-m-d H:i:s')
            // ]);
            # Update Journal
            // Journal::where('payment_id',$params['payment_id'])->update([
            //     'transaction_amount' => $params['amount'],
            //     'updated_at' => date('Y-m-d H:i:s')
            // ]);
            # Update Payment
            // Payment::where('id',$params['payment_id'])->update([
            //     'amount' => $params['amount'],
            //     'updated_at' => date('Y-m-d H:i:s')
            // ]);

            // dd($payment_collection);
            // foreach($invoices as $invoice){
            //     $payment_collection = PaymentCollection::where('store_id',$store_id)->where('is_approve', 1)->orderBy('cheque_date','asc')->get()->toArray();

            //     foreach($payment_collection as $collections){
            //         if($collections['collection_amount'] == $invoice['net_price']){
            //             InvoicePayment::insert([
            //                 'invoice_id' => $invoice['id'],
            //                 'payment_collection_id' => $collections['id'],
            //                 'invoice_no' => $invoice['invoice_no'],
            //                 'voucher_no' => $collections['vouchar_no'],
            //                 'invoice_amount' => $invoice['net_price'],
            //                 'vouchar_amount' => $collections['collection_amount'],
            //                 'paid_amount' => $collections['collection_amount'],
            //                 'rest_amount' => '',
            //                 'excess_amount' => '',
            //                 'created_at' => $collections['created_at'],
            //                 'updated_at' => $collections['created_at']
            //             ]);

            //         } else {
            //             if($collections['collection_amount'] < $invoice['net_price']){

            //             }
            //         }
            //     }
            // }
            dd($invoices);
            

        }
    }

    public function getCurrentStockBarcodes(Request $request)
    {
        # Get Current Stock Barcodes...
        $data = StockBox::select('id','product_id','barcode_no')->with('product:id,name')->where('is_stock_out', 0)->orderBy('product_id')->orderBy('barcode_no')->get()->toArray();

        // dd($data);
        $stock_data = StockBox::select('id','product_id','barcode_no',DB::raw("COUNT(id) AS qty"))->with('product:id,name')->where('is_stock_out', 0)->groupBy('product_id')->orderBy('product_id')->get()->toArray();

        // dd($stock_data);

        
        $myArr =  array();
        foreach($data as $item){
            
            $myArr[] = array(
                'product_name' => $item['product']['name'],
                'barcode_no' => $item['barcode_no']
            );
        }
        // dd($myArr);
        $fileName = "Stock-Barcodes".date('Y-m-d').".csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Product','Barcode');


        $callback = function() use($myArr, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);            

            foreach ($myArr as $item) {          
                $row['Product']  = $item['product_name'];
                $row['Barcode'] = $item['barcode_no'];
                                
                fputcsv($file, array($row['Product'], $row['Barcode'] ));                
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function change_numbering(Request $request)
    {
        // $product_id = $request->product_id;
        $barcode_no = $request->barcode_no;

        if(str_contains($barcode_no, 'RE')){
            echo 'Returned Barcode';
            $stock = StockBox::where('barcode_no', $barcode_no)->first();
            $product_id = $stock->product_id;
            // dd($product_id);

            $piece_price = StockProduct::where('product_id', $product_id)->whereHas('stock', function($stock){
                $stock->whereHas('purchase_order', function($po){
                    $po->whereNotNull('supplier_id');
                });
            })->max('piece_price');
            $unit_price = StockProduct::where('product_id', $product_id)->whereHas('stock', function($stock){
                $stock->whereHas('purchase_order', function($po){
                    $po->whereNotNull('supplier_id');
                });
            })->max('unit_price');

            dd('piece_price:- '.$piece_price.' & unit_price:- '.$unit_price);
        } else {
            echo 'Not returned barcode';
        }
        
    }

    
}
