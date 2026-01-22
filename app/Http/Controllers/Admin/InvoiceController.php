<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\OrderCancelledProduct;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\InvoicePayment;
use App\Models\InvoiceRevoke;
use App\Models\PackingslipNew1;
use App\Models\Packingslip;
use App\Models\PackingslipProduct;
use App\Models\PaymentCollection;
use App\Models\StockBox;
use App\Models\StockLog;
use App\Models\Ledger;
use App\Models\Changelog;
use App\Models\StaffCommision;

class InvoiceController extends Controller
{
    //
    public function index(Request $request)
    {
        # all...
        $paginate = 20;
        $term = !empty($request->term)?$request->term:'';
        $type = !empty($request->type)?$request->type:'';
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $store_name = !empty($request->store_name)?$request->store_name:'';
        $data = Invoice::with('order:id,order_no')->with('store:id,store_name,bussiness_name');
        $total = Invoice::select();
        

        if(!empty($term)){
            $data = $data->where('invoice_no','LIKE','%'.$term.'%')->orWhereHas('order', function($ord) use($term){
                $ord->where('order_no','LIKE','%'.$term.'%');
            })->orWhereHas('packingslip', function ($ps) use($term){
                $ps->where('slipno','LIKE','%'.$term.'%');
            });
            $total = $total->where('invoice_no','LIKE','%'.$term.'%')->orWhereHas('order', function($ord) use($term){
                $ord->where('order_no','LIKE','%'.$term.'%');
            })->orWhereHas('packingslip', function ($ps) use($term){
                $ps->where('slipno','LIKE','%'.$term.'%');
            });
        }

        if(!empty($store_id)){
            $data = $data->where('store_id', $store_id);
            $total = $total->where('store_id', $store_id);
        }

        if(!empty($type)){
            if($type == 'gst'){
                $data = $data->where('is_gst', 1);
                $total = $total->where('is_gst', 1);
            } else if ($type == 'non_gst'){
                $data = $data->where('is_gst', 0);
                $total = $total->where('is_gst', 0);
            }
            
        }

        $total = $total->count();
        $data = $data->orderBy('id','desc')->paginate($paginate);
        // dd($data);
        $data = $data->appends([
            'term'=>$term,
            'page'=>$request->page,
            'type'=>$type,
            'store_id' => $store_id,
            'store_name' => $store_name
        ]);
        
        return view('admin.invoice.index', compact('data','term','type','total','paginate','store_id','store_name'));
    }

    /*public function store($id,Request $request)
    {
        $paginate = 20;
        $term = !empty($request->term)?$request->term:'';
        
        $user_name = '';
        $store = DB::table('stores')->find($id);
        $user_name = !empty($store->bussiness_name)?$store->bussiness_name:$store->store_name;
        

        $data = Invoice::where('store_id',$id);
        $count_data = Invoice::where('store_id',$id);
        
        if(!empty($term)){
            $data = $data->where('invoice_no', 'LIKE', '%'.$term.'%')->orWhereHas('packingslip', function($ps) use ($term){
                $ps->where('slipno', 'LIKE', '%'.$term.'%');
            })->orWhereHas('order', function($ps) use ($term){
                $ps->where('order_no', 'LIKE', '%'.$term.'%');
            });

            $count_data = $count_data->where('invoice_no', 'LIKE', '%'.$term.'%')->orWhereHas('packingslip', function($ps) use ($term){
                $ps->where('slipno', 'LIKE', '%'.$term.'%');
            })->orWhereHas('order', function($ps) use ($term){
                $ps->where('order_no', 'LIKE', '%'.$term.'%');
            });
        }        
        $data = $data->orderBy('id','desc')->paginate($paginate);
        $count_data = $count_data->count();

        // dd($data);

        $data = $data->appends(['term'=>$term,'page'=>$request->page]);            
        return view('admin.invoice.store', compact('data','count_data','term','user_name','id'));
        
    }*/

    /*public function staff($id,Request $request)
    {
        $paginate = 20;
        $term = !empty($request->term)?$request->term:'';
        
        $user_name = '';
        $staff = DB::table('users')->find($id);
        $user_name = $staff->name;

        $data = Invoice::where('user_id',$id);

        if(!empty($term)){
            $data = $data->where('invoice_no', 'LIKE', '%'.$term.'%')->orWhereHas('packingslip', function($ps) use ($term){
                $ps->where('slipno', 'LIKE', '%'.$term.'%');
            })->orWhereHas('order', function($ps) use ($term){
                $ps->where('order_no', 'LIKE', '%'.$term.'%');
            });
        }

        $data = $data->orderBy('id','desc')->paginate($paginate);

        $data = $data->appends(['term'=>$term,'page'=>$request->page]); 
        return view('admin.invoice.staff', compact('data','term','user_name','id'));
        
    }*/

    public function payments(Request $request,$id,$user_id=0,$user_type='')
    {
        # payments covered invoices ...        

        $data = InvoicePayment::with(['invoice' => function($i){
            $i->with(['order' => function($o){
                $o->with('users');
            }]);
        }])->with(['paymentcollection' => function($p){
            $p->with('users');
        }])->where('invoice_id',$id);
        
        $data = $data->orderBy('id','desc')->get();

        // dd($data);
        return view('admin.invoice.payment', compact('data','user_type','user_id'));
    }

    public function edit(Request $request,$id)
    {
        $invoice = Invoice::find($id);
        $invoice_products = InvoiceProduct::where('invoice_id',$id)->get();
        // dd($invoice);
        return view('admin.invoice.edit', compact('id','invoice','invoice_products'));
    }

    public function update(Request $request)
    {
        # Invoice Update
        $request->validate([
            'details.*.product_id' => 'required',            
            'details.*.hsn_code' => 'required',
            'details.*.pcs' => 'required|not_in:0',
            'details.*.piece_price' => 'required|not_in:0',
            'details.*.quantity' => 'required|not_in:0'            
        ],[
            'details.*.product_id.required' => 'Please add product',
            'details.*.hsn_code.required' => 'Please add hsn code of product',
            'details.*.pcs.required' => 'Please add pices per carton',
            'details.*.pcs.not_in' => 'Please add pices per carton',
            'details.*.piece_price.required' => 'Please add price per piece',
            'details.*.piece_price.not_in' => 'Zero price cannot be used',
            'details.*.quantity.required' => 'Please add number of carton',
            'details.*.quantity.not_in' => 'Please add number of carton'
        ]);
        $params = $request->except('_token');
        // dd($params);
        $invoice = Invoice::find($params['invoice_id']);
        $invoice_no = $invoice->invoice_no;
        $invoice_payment_amount = InvoicePayment::where('invoice_id',$params['invoice_id'])->sum('paid_amount');

        
        $required_payment_amount = ($params['net_price'] - $invoice_payment_amount);
        // dd($required_payment_amount);
        
        $old_invoice_products = InvoiceProduct::select('product_id')->where('invoice_id',$params['invoice_id'])->get();

        $oldProIds = $currentProIds = $removeProIdArr = array();
        foreach($old_invoice_products as $pro){
            $oldProIds[] = $pro->product_id;
        }
        // echo 'oldProIds:- <pre>'; print_r($oldProIds);        
        $details = $params['details'];
        $isDisbursed = true;
        foreach($details as $newItem){
            $currentProIds[] = $newItem['product_id'];            
        }
        // echo 'currentProIds:- <pre>'; print_r($currentProIds);
        foreach($oldProIds as $value){
            if(!in_array($value,$currentProIds)){
                $removeProIdArr[] = $value;
            }
        }
        // echo 'removeProIdArr:- <pre>'; print_r($removeProIdArr);        
        // die;
        if(!empty($removeProIdArr)){
            foreach($removeProIdArr as $value){                
                #1:- Remove Order Items
                OrderProduct::where('order_id',$params['order_id'])->where('product_id',$value)->delete();     
                #2:- Remove Invoice Product
                InvoiceProduct::where('invoice_id',$params['invoice_id'])->where('product_id',$value)->delete();           
                #3:- Remove Packing Slip Product
                Packingslip::where('packingslip_id',$params['packingslip_id'])->where('product_id',$value)->delete();                
                #5:- Unscan Stock Boxes
                StockBox::where('packingslip_id',$params['packingslip_id'])->where('product_id',$value)->update([
                    'packingslip_id' => null,
                    'slip_no' => null,
                    'is_scanned' => 0,
                    'is_stock_out' => 0,
                    'scan_no' => null,
                    'stock_out_weight_val' => null
                ]);
                
            }
        }
        

        foreach($details as $item){                        
            if($item['isNoCtnChanged'] == 1){
                $isDisbursed = false;                
                $editOrdProArr = array(
                    'product_name' => $item['product'],
                    'piece_price' => $item['piece_price'],
                    'qty' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'release_qty' => $item['quantity'],
                    'price' => ($item['propcs'] * $item['piece_price']),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $editInvProArr = array(
                    'product_name' => $item['product'],
                    'quantity' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'single_product_price' => $item['piece_price'],
                    'price' => $item['price'],
                    'count_price' => $item['count_price'],
                    'total_price' => $item['total_price'],
                    'hsn_code' => $item['hsn_code'],
                    'updated_at' => date('Y-m-d H:i:s')
                );
                
                // dd($editOrdProArr);
                #1:- Update Order Product
                OrderProduct::where('order_id',$params['order_id'])->where('product_id',$item['product_id'])->update($editOrdProArr);
                #2:- Update Invoice Product
                InvoiceProduct::where('invoice_id',$params['invoice_id'])->where('product_id',$item['product_id'])->update($editInvProArr);
                #2:- Update Packing Slip Product Quantity
                Packingslip::where('packingslip_id',$params['packingslip_id'])->where('product_id',$item['product_id'])->update([
                    'quantity' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
            }
            if($item['isNoCtnChanged'] == 0){
                // dd($item['total_price']);
                $editOrdProArr = array(
                    'product_name' => $item['product'],
                    'piece_price' => $item['piece_price'],
                    'qty' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'release_qty' => $item['quantity'],
                    'price' => ($item['propcs'] * $item['piece_price']),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $editInvProArr = array(
                    'product_name' => $item['product'],
                    'quantity' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'single_product_price' => $item['piece_price'],
                    'price' => $item['price'],
                    'count_price' => $item['count_price'],
                    'total_price' => $item['total_price'],
                    'hsn_code' => $item['hsn_code'],
                    'updated_at' => date('Y-m-d H:i:s')
                );
                
                #1:- Update Order Product
                OrderProduct::where('order_id',$params['order_id'])->where('product_id',$item['product_id'])->update($editOrdProArr);
                #2:- Update Invoice Product
                InvoiceProduct::where('invoice_id',$params['invoice_id'])->where('product_id',$item['product_id'])->update($editInvProArr);
                #3:- Update Packing Slip Product Quantity
                Packingslip::where('packingslip_id',$params['packingslip_id'])->where('product_id',$item['product_id'])->update([
                    'quantity' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
            }
            if($item['oldCtnNo'] == null){
                $isDisbursed = false;
                $addOrdProArr = array(
                    'order_id' => $params['order_id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product'],
                    'piece_price' => $item['piece_price'],
                    'qty' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'price' => ($item['propcs'] * $item['piece_price']),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $addInvProArr = array(
                    'invoice_id' => $params['invoice_id'],
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product'],
                    'quantity' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'single_product_price' => $item['piece_price'],
                    'price' => $item['price'],
                    'total_price' => $item['total_price'],
                    'hsn_code' => $item['hsn_code'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                // dd($addOrdProArr);
                #1:- Add New Order Product
                OrderProduct::insert($addOrdProArr);
                #2:- Add New PS
                Packingslip::insert([
                    'packingslip_id' => $params['packingslip_id'],
                    'order_id' => $params['order_id'],
                    'product_id' => $item['product_id'],
                    'slip_no' => $params['slip_no'],
                    'quantity' => $item['quantity'],
                    'pcs' => $item['pcs'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                InvoiceProduct::insert($addInvProArr);
            }
        }

        Order::where('id',$params['order_id'])->update([
            'amount' => $params['net_price'],
            'final_amount' => $params['net_price'],
            'comment' => 'Re edited via updating invoice'
        ]);

        Invoice::where('id',$params['invoice_id'])->update([
            'net_price' => $params['net_price'],
            'required_payment_amount' => $required_payment_amount,
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => Auth::user()->id
        ]);

        ## Bills Update ##
        DB::table('bills')->where('invoice_id', $params['invoice_id'])->update([
            'amount' => $params['net_price'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $store_id = $invoice->store_id;
        $paymentIds = $collection_data = array();
        $old_payment_collections = PaymentCollection::where('store_id',$store_id)->orderBy('cheque_date','asc')->get();
        foreach($old_payment_collections as $collections){
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
        if(!empty($collection_data)){
            $this->resetInvoicePayments($store_id,$params['invoice_id'],$collection_data);
        }        

        Ledger::where('transaction_id',$invoice_no)->update(['transaction_amount' => $params['net_price']]);
        /* Changelog Entry */
        Changelog::insert([
            'doneby' => Auth::user()->id,
            'purpose' => 'edit_invoice',
            'data_details' => json_encode($params),
            'created_at' => date('Y-m-d H:i:s')
        ]);               

        if(!$isDisbursed){
            // die('Not disburseable, Need to scan again. Need to redirect packing slip goods scan out page');
            Session::flash('message', 'Need to scan again'); 
            PackingslipNew1::where('id',$params['packingslip_id'])->update(['is_disbursed' => 0]);
            
            StockBox::where('packingslip_id',$params['packingslip_id'])->update([
                'packingslip_id' => null,
                'slip_no' => null,
                'is_scanned' => 0,
                'is_stock_out' => 0,
                'scan_no' => null,
                'stock_out_weight_val' => null
            ]);
            # Delete Stock Log
            StockLog::where('packingslip_id',$params['packingslip_id'])->delete();
            return redirect()->route('admin.packingslip.view_goods_stock',$params['packingslip_id']);
        } else {
            // die('Disburseable, No need to scan any cartons. Simply redirect in invoice list');
            $this->editStockLogInvoiceEdit($params['packingslip_id']);
            Session::flash('message', 'Invoice updated successfully'); 
            PackingslipNew1::where('id',$params['packingslip_id'])->update(['is_disbursed' => 1]);
            return redirect()->route('admin.invoice.index');
        }

    }

    private function editStockLogInvoiceEdit($packingslip_id){
        $packing_slip = Packingslip::where('packingslip_id',$packingslip_id)->get();
        foreach($packing_slip as $item){
            $getOrderProductDetails = getOrderProductDetails($item->order_id,$item->product_id);
            $piece_price = $getOrderProductDetails->piece_price;
            $carton_price = $getOrderProductDetails->price;
            $total_price = ($item->quantity * $carton_price);
            StockLog::insert([
                'quantity' => $item->quantity,
                'piece_price' => $piece_price,
                'carton_price' => $carton_price,
                'total_price' => $total_price,
                'created_at'=>date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function revoke(Request $request,$id)
    {
        # Revoke Invoice / Delete Invoice
        
        $invoice = Invoice::find($id);
        $invoice_no = $invoice->invoice_no;
        $invoice_products = InvoiceProduct::where('invoice_id',$id)->get();
        $invoice->invoice_products = $invoice_products;
        $invoice_data_json = json_encode($invoice);

        $packingslip_id = $invoice->packingslip_id;
        $order_id = $invoice->order_id;
        $store_id = $invoice->store_id;

        $all_invoices = Invoice::where('store_id',$store_id)->where('id','!=',$id)->get();
        # Store All Invoice
        $all_inv_ids = Invoice::where('store_id',$store_id)->pluck('id')->toArray();
        // dd($all_inv_ids);

        // dd($all_invoices);
        
        $invoiceIds = array();
        if(!empty($all_invoices)){
            foreach($all_invoices as $invoice){
                $invoiceIds[] = $invoice->id;
                # Revert Invoice Required Amount to Net Amount and All Payment Status
                Invoice::where('id',$invoice->id)->update([
                    'required_payment_amount' => $invoice->net_price,
                    'payment_status' => 0,
                    'is_paid' => 0
                ]);
            }
        }
        // dd($invoiceIds);
        $paymentIds = $collection_data = $invoice_payment_data = array();        
        
        $store_payment_collections = PaymentCollection::where('store_id',$store_id)->where('is_approve', 1)->get();
        foreach($store_payment_collections as $collections){
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

        $store_collection_json = json_encode($collection_data);
        $invoiceRevoke = array(
            'store_id' => $store_id,
            'order_id' => $order_id,
            'invoice_no' => $invoice_no,
            'done_by' => Auth::user()->id,
            'invoice_data_json' => $invoice_data_json,
            'store_collection_json' => $store_collection_json,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        InvoiceRevoke::insert($invoiceRevoke);
        # Unscan Stock Boxes Packing Slip
        $unscanStockBox = array(
            'is_scanned' => 0,
            'is_stock_out' => 0,
            'packingslip_id' => null,
            'slip_no' => null,
            'scan_no' => null,
            'stock_out_weight_val' => null
        );
        StockBox::where('packingslip_id',$packingslip_id)->where('is_scanned',1)->where('is_stock_out',1)->update($unscanStockBox);
        # Stock Log Entry Delete On PS Id
        StockLog::where('packingslip_id',$packingslip_id)->delete();
        ###### Deletes ######        
        # Delete Packing Slip Products
        Packingslip::where('packingslip_id', $packingslip_id)->delete();       
        # Delete Packing Slip
        PackingslipNew1::where('id', $packingslip_id)->delete(); 
        # Ledger Invoice Delete
        Ledger::where('transaction_id',$invoice_no)->delete();        
        # Ledger Delete If There Found On That Invoice On Staff Commision
        $staff_commision_ledger = StaffCommision::where('invoice_id',$id)->get();
        $staffCommId = array();
        if(!empty($staff_commision_ledger)){
            foreach($staff_commision_ledger as $comm){
                $staffCommId[] = $comm->id;
            }
        }
        Ledger::whereIn('staff_commision_id',$staffCommId)->delete();
        # Staff Commision Delete If There Found On That Invoice
        StaffCommision::where('invoice_id',$id)->delete();
        # Delete Invoice Products
        InvoiceProduct::where('invoice_id',$id)->delete();
        #Store Invoice Payments Delete
        if(!empty($all_inv_ids)){
            InvoicePayment::whereIn('invoice_id',$all_inv_ids)->delete();
        }
        
            
        $commisionIds = array();
        $staff_commisions = StaffCommision::whereIn('invoice_id',$invoiceIds)->get();
        foreach($staff_commisions as $comm){
            $commisionIds[] = $comm->id;
        }
        # Delete Staff Commision Ledger On Other
        Ledger::whereIn('staff_commision_id',$commisionIds)->delete();
        # Delete Staff Commision On Other
        StaffCommision::whereIn('id',$commisionIds)->delete();
        ##### ##### #####

        
        
        # Order Status Cancelled || Cancel Order Reason With "Cancelled Due To Revoke Invoice"
        $updateOrder = array(
            'status' => 3,
            'comment' => "Cancelled Due To Revoke Invoice"            
        );
        Order::where('id',$order_id)->update($updateOrder);
        $order_products = OrderProduct::where('order_id',$order_id)->get();
        if(!empty($order_products)){
            foreach($order_products as $op){
                OrderCancelledProduct::insert([
                    'order_id' => $order_id,
                    'product_id' => $op->product_id,
                    'qty' => ($op->qty - $op->release_qty),
                    'created_at'=>date('Y-m-d H:i:s')
                ]);
            }
            OrderProduct::where('order_id',$order_id)->where('product_id',$op->product_id)->update(['release_qty'=>0]);
        }

        $this->resetInvoicePayments($store_id,$id,$collection_data);
        // // die;

        # Delete Invoice
        Invoice::where('id',$id)->delete();

        Session::flash('message', 'Invoice revoked successfully');
        return redirect()->route('admin.invoice.index');

    }

    private function resetInvoicePayments($store_id,$invoice_id,$collection_data)
    {
        foreach($collection_data as $payments){
            // dd($invoice_id);
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
                $invoice = Invoice::where('store_id',$store_id)->where('id','!=',$invoice_id)->where('is_paid', 0)->orderBy('id','asc')->get();
                
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

    public function barcode($id)
    {
        # show barcodes ...
        
        $invoice = Invoice::find($id);
        $packingslip_id = $invoice->packingslip_id;
        
        $ps_box = StockBox::where('packingslip_id',$packingslip_id)->get();
        
        return view('admin.invoice.barcode',compact('ps_box','invoice'));
    }
}
