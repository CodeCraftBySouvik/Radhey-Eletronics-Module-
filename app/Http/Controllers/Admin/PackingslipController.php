<?php



namespace App\Http\Controllers\Admin;



use App\Interfaces\OrderInterface;

use App\Models\Order;

use App\Models\OrderProduct;

use App\Models\Store;

use App\Models\StockBox;

use App\Models\Packingslip;

use App\Models\PackingslipNew1;

use App\Models\Invoice;

use App\Models\InvoiceProduct;

use App\Models\InvoicePayment;

use App\Models\StaffCommision;

use App\Models\Ledger;

use App\Models\Changelog;

use App\Models\PaymentCollection;

use App\Models\StockLog;
use App\Models\WhatsAppInvoice;

use App\User;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Http\Response;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Session;

use Illuminate\Support\Facades\File; 

use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Support\Facades\Auth;



class PackingslipController extends Controller

{

    public function index(Request $request)

    {

        $paginate = 20;

        $search = !empty($request->search)?$request->search:'';

        $search_product_name = !empty($request->search_product_name)?$request->search_product_name:'';

        $search_product_id = !empty($request->search_product_id)?$request->search_product_id:'';

        $data = PackingslipNew1::with('packingslip_products');

        $countData = PackingslipNew1::select('*');

        

        if(!empty($search)){

            $data = $data->where('slipno','LIKE','%'.$search.'%')->orWhereHas('store', function($store) use($search){

                $store->where('store_name','LIKE','%'.$search.'%')->orWhere('bussiness_name','LIKE','%'.$search.'%');

            })->orWhereHas('order', function($o) use ($search){

                $o->where('order_no','LIKE','%'.$search.'%');

            });

            $countData = $countData->where('slipno','LIKE','%'.$search.'%')->orWhereHas('store', function($store) use($search){

                $store->where('store_name','LIKE','%'.$search.'%')->orWhere('bussiness_name','LIKE','%'.$search.'%');

            })->orWhereHas('order', function($o) use ($search){

                $o->where('order_no','LIKE','%'.$search.'%');

            });;

        }



        if(!empty($search_product_id)){

            $data = $data->whereHas('packingslip_products', function ($pro) use($search_product_id){

                $pro->where('product_id',$search_product_id);

            });

            $countData = $countData->whereHas('packingslip_products', function ($pro) use($search_product_id){

                $pro->where('product_id',$search_product_id);

            });

        }



        $data = $data->orderBy('id','desc')->paginate($paginate);



        $data = $data->appends([

            'page' => $request->page,

            'search' => $search,

            'search_product_id' => $search_product_id,

            'search_product_name' => $search_product_name

        ]);

        $countData = $countData->count();



        // dd($data);



        return view('admin.packingslip.index', compact('data','countData','search','search_product_id','search_product_name','paginate'));

    }
	
	public function order_in_progress(Request $request){
		 $paginate = 20;

        $search = !empty($request->search)?$request->search:'';

        $search_product_name = !empty($request->search_product_name)?$request->search_product_name:'';

        $search_product_id = !empty($request->search_product_id)?$request->search_product_id:'';

        $data = PackingslipNew1::with('packingslip_products')->where('is_disbursed', 1); ;

        $countData = PackingslipNew1::select('*')->where('is_disbursed', 1); ;

        

        if(!empty($search)){

            $data = $data->where('slipno','LIKE','%'.$search.'%')->orWhereHas('store', function($store) use($search){

                $store->where('store_name','LIKE','%'.$search.'%')->orWhere('bussiness_name','LIKE','%'.$search.'%');

            })->orWhereHas('order', function($o) use ($search){

                $o->where('order_no','LIKE','%'.$search.'%');

            });

            $countData = $countData->where('slipno','LIKE','%'.$search.'%')->orWhereHas('store', function($store) use($search){

                $store->where('store_name','LIKE','%'.$search.'%')->orWhere('bussiness_name','LIKE','%'.$search.'%');

            })->orWhereHas('order', function($o) use ($search){

                $o->where('order_no','LIKE','%'.$search.'%');

            });;

        }



        if(!empty($search_product_id)){

            $data = $data->whereHas('packingslip_products', function ($pro) use($search_product_id){

                $pro->where('product_id',$search_product_id);

            });

            $countData = $countData->whereHas('packingslip_products', function ($pro) use($search_product_id){

                $pro->where('product_id',$search_product_id);

            });

        }



        $data = $data->orderBy('id','desc')->paginate($paginate);



        $data = $data->appends([

            'page' => $request->page,

            'search' => $search,

            'search_product_id' => $search_product_id,

            'search_product_name' => $search_product_name

        ]);

        $countData = $countData->count();

        return view('admin.packingslip.order_in_progress', compact('data','countData','search','search_product_id','search_product_name','paginate'));
	}

    

    public function add(Request $request,$order_id)

    {

        $search = !empty($request->search)?$request->search:'';

        $order_products = DB::table('order_products AS op')->select('op.product_id','op.qty','op.release_qty','p.name AS pro_name','p.pcs')->leftJoin('products AS p', 'p.id','op.product_id')->where('op.order_id',$order_id)->get()->toarray();

        $order = DB::table('orders')->find($order_id);



        // dd($order_products);

        return view('admin.packingslip.add', compact('order','order_products','order_id','search'));

    }



    public function save(Request $request)

    {

        # save packing slip with product and ctn quantity...

        $request->validate([

            'order_id' => 'required',

            'details.*.quantity' => 'required', 

        ],[

            'order_id.required' => 'Please add order',

            'details.*.quantity.required' => 'Please add quantity',

        ]);



        if(empty($request->details)){

            return redirect()->back()->withErrors(['order_id'=> "No product items with disburse quantity"])->withInput(); 

        }



        $params = $request->except('_token');

        $slip_no = date('YmdHis');

        // dd($params);

        $packingslip_id = PackingslipNew1::insertGetId([

            'order_id' => $params['order_id'],

            'store_id' => $params['store_id'],

            'slipno' => $slip_no,

            'created_at' => date('Y-m-d H:i:s'),

            'updated_at' => date('Y-m-d H:i:s'),

            'created_by' => Auth::user()->id

        ]);

        $details = $params['details'];

        foreach($details as $item){

            Packingslip::insert([

                'packingslip_id' => $packingslip_id,

                'order_id' => $params['order_id'],

                'product_id' => $item['product_id'],

                'slip_no' => $slip_no,

                'quantity' => $item['quantity'],

                'pcs' => $item['pcs'],

                'created_at' => date('Y-m-d H:i:s'),

                'updated_at' => date('Y-m-d H:i:s')

            ]);



            OrderProduct::where('order_id',$params['order_id'])->where('product_id',$item['product_id'])->update([

                'release_qty' => $item['quantity'],

                'updated_at' => date('Y-m-d H:i:s')

            ]);

        }



        updateSalesOrderStatusPS($params['order_id']);



        Session::flash('message', 'Packing slip generated successfully'); 

        return redirect()->route('admin.packingslip.index');     

        

    }



    public function edit(Request $request,$id)

    {

        $packingslip = PackingSlipNew1::find($id);

        if(!empty($packingslip->is_disbursed)){

            Session::flash('message', 'Packing slip already disbursed'); 

            return redirect()->route('admin.packingslip.index'); 

        }

        $packing_slip = Packingslip::where('packingslip_id',$id)->get();

        // dd($packing_slip);

        return view('admin.packingslip.edit',compact('id','packingslip','packing_slip'));

    }



    public function update(Request $request,$id)

    {

        # Packings Slip Update

        // die('We are working on the edit packing slip section. Please back');

        $request->validate([

            'details.*.quantity' => 'required|not_in:0',

            'details.*.maxstock' => 'required|not_in:0',

            'details.*.product_id' => 'required'

        ]);



        $params = $request->except('_token');

        

        $packingslip = PackingslipNew1::find($id);

        $slipno = $packingslip->slipno;



        $details = $params['details'];

        $oldProIds = $currentProIds = $removeProIdArr = array();

        $oldProducts = Packingslip::where('packingslip_id',$id)->get();

        foreach($oldProducts as $value){

            $oldProIds[] = $value->product_id;

        }

        // echo 'oldProIds:- <pre>'; print_r($oldProIds);

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

        // dd($params);

        if(!empty($removeProIdArr)){

            foreach($removeProIdArr as $value){

                #1: Delete Packing Slip Product

                Packingslip::where('packingslip_id',$id)->where('product_id',$value)->delete();

                #2: Delete Order Product

                OrderProduct::where('order_id',$params['order_id'])->where('product_id',$value)->delete();

                #3: Unscan Stock Box

                StockBox::where('packingslip_id')->where('product_id',$value)->update([

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

            if($item['isChanged'] == 1){

                // die('Changed');

                Packingslip::where('packingslip_id',$id)->where('product_id',$item['product_id'])->update([

                    'quantity' => $item['quantity'],

                    'pcs' => $item['pcs'],

                    'updated_at' => date('Y-m-d H:i:s')

                ]);

                # Order Product Update

                OrderProduct::where('order_id',$params['order_id'])->where('product_id',$item['product_id'])->update([

                    'qty' => $item['quantity'],

                    'pcs' => $item['pcs'],

                    'release_qty' => $item['quantity'],

                    'updated_at' => date('Y-m-d H:i:s')

                ]);            

                # Unscan Stock Boxes

                StockBox::where('packingslip_id',$id)->update([

                    'is_scanned' => 0,

                    'is_stock_out' => 0,

                    'packingslip_id' => null,

                    'slip_no' => null,

                    'scan_no' => null,

                    'stock_out_weight_val' => null

                ]);

            }            

        }



        PackingslipNew1::where('id',$id)->update([

            'is_disbursed' => 0,

            'updated_at' => date('Y-m-d H:i:s'),

            'updated_by' => Auth::user()->id

        ]);



        $total_order_amount = OrderProduct::where('order_id',$params['order_id'])->selectRaw("SUM(price*qty) AS amount")->get();

        $amount = $total_order_amount[0]->amount;



        Order::where('id',$params['order_id'])->update([

            'amount' => $amount,

            'final_amount' => $amount,

            'status' => 4

        ]);



        /* Changelog */

        Changelog::insert([

            'doneby' => Auth::user()->id,

            'purpose' => 'edit_ps',

            'data_details' => json_encode($params),

            'created_at' => date('Y-m-d H:i:s')

        ]);



        Session::flash('message', 'Packing slip updated successfully'); 

        return redirect()->route('admin.packingslip.index'); 

        

    }



    public function revoke(Request $request,$id)

    {

        

        $packingslip = PackingslipNew1::find($id);

        $order_id = $packingslip->order_id;

        // dd($order_id);

        #1: Unscan Stock Box

        $updateStockBox = array(

            'packingslip_id' => null,

            'slip_no' => null,

            'is_scanned' => 0,

            'is_stock_out' => 0,

            'scan_no' => null,

            'stock_out_weight_val' => null



        );

        StockBox::where('packingslip_id',$id)->update($updateStockBox);

        

        #2: Delete Packing Slip Product

        Packingslip::where('packingslip_id',$id)->delete();

        #3: Delete Packingslip

        PackingslipNew1::where('id',$id)->delete();

        #4: Order Status Reverted to Received 

        Order::where('id',$order_id)->update([

            'status' => 1

        ]);

        #5: Revert release qty and release pcs order product

        OrderProduct::where('order_id',$order_id)->update([

            'release_qty' => 0,

            'release_pcs' => 0

        ]);

        #6: Land to Edit Order Page

        Session::flash('message', 'Packingslip Revoked Successfully. Please update the order');

        return redirect()->route('admin.order.edit',$order_id);

    }

    

    public function get_pdf($slip_no)

    {

        $packingslip = PackingslipNew1::where('slipno',$slip_no)->first();

        $data = DB::table('packing_slip AS ps')->select('ps.*','p.name AS pro_name','o.order_no','o.created_at AS ordered_at','s.store_name','s.whatsapp AS store_whatsapp','s.bussiness_name')->leftJoin('orders AS o','o.id','ps.order_id')->leftJoin('products AS p','p.id','ps.product_id')->leftJoin('stores AS s','s.id','o.store_id')->where('ps.slip_no',$slip_no)->get();



        // dd($slip_no);



        $pckngpdfname = $slip_no."";



        $pdf = Pdf::loadView('admin.packingslip.pdf', compact('data','packingslip'));

        return $pdf->download($pckngpdfname.'.pdf');



        // return view('admin.packingslip.pdf', compact('data'));

    }



    public function raise_invoice_form($id)

    {

        # $id = packingslip_id

        # check invoice raised for PS



        $packingslips = PackingslipNew1::find($id);        

        if(!empty($packingslips->invoice_id)){

            Session::flash('message', "Invoice raised already for this packing slip");

            return redirect()->route('admin.packingslip.index');

        }        

        $packing_slip = Packingslip::where('packingslip_id',$id)->get();

        // dd($packing_slip);



        // $data = DB::table('packing_slip AS ps')

        // ->select('ps.*','p.name AS pro_name','p.igst','p.cgst','p.sgst','p.hsn_code','o.order_no','o.user_id','o.created_at AS ordered_at','s.store_name','s.bussiness_name','s.whatsapp AS store_whatsapp','s.email AS store_email','s.contact AS store_contact','s.address_outstation','s.billing_address AS store_billing_address','s.billing_landmark AS store_billing_landmark','s.billing_state AS store_billing_state','s.billing_city AS store_billing_city','s.billing_pin AS store_billing_pin','s.shipping_address AS store_shipping_address','s.shipping_landmark AS store_shipping_landmark','s.shipping_state AS store_shipping_state','s.shipping_city AS store_shipping_city','s.shipping_pin AS store_shipping_pin','o.store_id','o.is_gst')

        // ->leftJoin('orders AS o','o.id','ps.order_id')

        // ->leftJoin('products AS p','p.id','ps.product_id')

        // ->leftJoin('stores AS s','s.id','o.store_id')             

        // ->where('ps.slip_no',$slip_no)->get();



        // dd($data);

        // return view('admin.packingslip.raise-invoice', compact('data'));



       

        return view('admin.packingslip.raise-invoice', compact('id','packingslips','packing_slip'));

    }



    public function save_invoice(Request $request)

    {        

        $params = $request->except('_token');        

        $trn_file = '';

        // dd($params);       

        $invoice_no = genAutoIncreNoInv();

        $sum_total_price = 0;

        $net_price = $params['net_price'];

        $id = Invoice::insertGetId([

            'packingslip_id' => $params['packingslip_id'],

            'invoice_no' => $invoice_no,

            'net_price' => $net_price,

            'required_payment_amount' => $net_price,

            'order_id' => $params['order_id'],

            'store_id' => $params['store_id'],

            'user_id' => $params['user_id'],

            'is_gst' => $params['is_gst'],            

            'store_address_outstation' => $params['store_address_outstation'],                  

            'created_at' => date('Y-m-d H:i:s'),

            'updated_at' => date('Y-m-d H:i:s'),

            'created_by' => Auth::user()->id

        ]);
        if($id){
            $wInvoice = new WhatsAppInvoice;
            $wInvoice->packingslip_id = $params['packingslip_id'];
            $wInvoice->invoice_id = $id;
            $wInvoice->invoice_no = $invoice_no;
            $wInvoice->net_price = $net_price;
            $wInvoice->required_payment_amount = $net_price;
            $wInvoice->order_id = $params['order_id'];
            $wInvoice->store_id = $params['store_id'];
            $wInvoice->user_id = $params['user_id'];
            $wInvoice->is_gst = $params['is_gst'];
            $wInvoice->store_address_outstation = $params['store_address_outstation'];
            $wInvoice->created_at = date('Y-m-d H:i:s');
            $wInvoice->updated_at = date('Y-m-d H:i:s');
            $wInvoice->created_by = Auth::user()->id;
            $wInvoice->save();
        }

        /* invoice_products entry */

        $details = $params['details'];

        // $productIdArr = array();

        foreach($details as $item){

            // $productIdArr[] = $item['product_id'];

            $getOrderProductDetails = getOrderProductDetails($params['order_id'],$item['product_id']);

                        

            InvoiceProduct::insert([

                'invoice_id' => $id,

                'product_id' => $item['product_id'],

                'product_name' => $item['product_name'],

                'quantity' => $item['quantity'],

                'pcs' => $item['pcs'],

                'price' => $item['price'],

                'single_product_price' => $item['single_product_price'],

                'count_price' => $item['count_price'],

                'total_price' => $item['total_price'],

                'is_store_address_outstation' => $item['is_store_address_outstation'],

                'hsn_code' => $item['hsn_code'],

                'igst' => $item['igst'],

                'cgst' => $item['cgst'],

                'sgst' => $item['sgst'],

                'created_at' => date('Y-m-d H:i:s'),

                'updated_at' => date('Y-m-d H:i:s')

            ]);

            

        } 



        ## Insert Bill ##

        DB::table('bills')->insert([            

            'store_id' => $params['store_id'],

            'invoice_id' => $id,

            'transaction_id' => $invoice_no,

            'entry_date' => date('Y-m-d'),

            'amount' =>  $net_price,

            'created_at' => date('Y-m-d H:i:s'),

            'updated_at' => date('Y-m-d H:i:s')          

        ]);



        /* Change status invoice generated in packing slip */



        PackingslipNew1::where('id',$params['packingslip_id'])->update(['invoice_id' => $id]);



        /* ledger entry */



        $bank_cash = 'bank';

        $is_gst = 1;

        if($params['is_gst'] == 0){

            $bank_cash = 'cash';

            $is_gst = 0;

        }

        

        Ledger::insert([

            'user_type' => 'store',

            'store_id' => $params['store_id'],

            'transaction_id' => $invoice_no,

            'transaction_amount' => $net_price,

            'is_debit' => 1,

            'bank_cash' => $bank_cash,

            'is_gst' => $is_gst,

            'entry_date' => date('Y-m-d'),

            'purpose' => 'invoice',

            'purpose_description' => 'invoice raised of sales order for store',

            'created_at' => date('Y-m-d H:i:s'),

            'updated_at' => date('Y-m-d H:i:s')

        ]);



        /* ++++++++++++++ Set Reset Invoice Payments ++++++++++++++++++ */

        $store_payment_collections = PaymentCollection::where('store_id', $params['store_id'])->where('is_approve', 1)->get();

        $collection_data = array();

        if(!empty($store_payment_collections)){

            foreach($store_payment_collections as $collections){

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

                    'created_at' => $collections->created_at

                );

            }

        }



        $all_invoices = Invoice::where('store_id',$params['store_id'])->where('id','!=',$id)->get();

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

        

        $commisionIds = array();

        $staff_commisions = StaffCommision::whereIn('invoice_id',$invoiceIds)->get();

        foreach($staff_commisions as $comm){

            $commisionIds[] = $comm->id;

        }

       

        # Delete Old Staff Commision

        StaffCommision::whereIn('id',$commisionIds)->delete();

        # Delete Staff Commision Ledger

        Ledger::whereIn('staff_commision_id',$commisionIds)->delete();

        # Delete Old Invoice Payments

        if(!empty($invoiceIds)){

            InvoicePayment::whereIn('invoice_id',$invoiceIds)->delete();

        }

        

        $this->resetInvoicePayments($params['store_id'],$collection_data);



        /* ++++++++++++++++++++++++ ++++++++++++++++++++++++++++++++++++++++ */



        if(!empty($is_gst)){

            Session::flash('message', 'Invoice raised successfully for packing slip no '.$params['slip_no']); 

        } else {

            Session::flash('message', 'Non-GST Cash Slip generated successfully for packing slip no '.$params['slip_no']); 

        }

        

        return redirect()->route('admin.invoice.index');

    }

    

    public function upload_trn(Request $request)

    {

        # upload TRN pdf for invoice if store address found outstation...

        $params = $request->except('_token');

        $slip_no = $params['slip_no'];

        $invoice_no = $params['invoice_no'];

        $order_id = $params['order_id'];



        /* Existing file remove */
        $invoiceData = DB::table('invoice')->where('invoice_no',$invoice_no)->first();

        $old_trn_file = !empty($invoiceData->trn_file)?$invoiceData->trn_file:'';
        if(!empty($old_trn_file)){

            $file_path = public_path().'/'.$old_trn_file;

            // echo $old_trn_file;

            // echo '<br/>'; 

            // echo $file_path; 

            File::delete($file_path);

            // die;
        }



        $upload_path = "public/uploads/trn/";

        $pdf = $params['trn_file'];           

    

        $PdfName = time().".".$pdf->getClientOriginalName();

        $pdf->move($upload_path, $PdfName);

        $uploadedPdf = $PdfName;

        $trn_file= $upload_path.$uploadedPdf;

        

        DB::table('invoice')->where('invoice_no',$invoice_no)->update(['trn_file'=>$trn_file]);



        Session::flash('message', 'TRN file uploaded successfully for Invoice no '.$invoice_no); 

        return redirect()->route('admin.packingslip.index', $order_id);



    }



    public function view_invoice($invoice_no)

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



    public function view_goods_stock(Request $request,$id)

    {

        # code...

        $search = !empty($request->search)?$request->search:'';

        $packingslip = PackingslipNew1::find($id);

        $slip_no = $packingslip->slipno; 

        $packing_slip = Packingslip::where('packingslip_id',$id)->get();

        $proidArr = array();

        foreach($packing_slip as $item){

            $proidArr[] = $item->product_id;

        }        

        $data = StockBox::with('product')->whereIn('product_id',$proidArr)->where('is_stock_out', 0);

        $totalData = StockBox::with('product')->whereIn('product_id',$proidArr)->where('is_stock_out', 0);



        if(!empty($search)){

            

            $data = $data->where(function($q) use ($search){

                $q->where('barcode_no','LIKE', '%'.$search.'%')->orWhere('product_id', $search)->orWhereHas('product', function ($searchproduct) use ($search) {

                    $searchproduct->where('name', 'LIKE','%'.$search.'%');

                });

            });

            $totalData = $totalData->where(function($q) use ($search){

                $q->where('barcode_no','LIKE', '%'.$search.'%')->orWhere('product_id', $search)->orWhereHas('product', function ($searchproduct) use ($search) {

                    $searchproduct->where('name', 'LIKE','%'.$search.'%');

                });

            });

        }

        $data = $data->orderBy('barcode_no','asc')->get()->sortBy('product_id')->groupBy('product.id');

        $totalData = $totalData->count();



        $total_checkbox = Packingslip::where('slip_no',$slip_no)->sum('quantity');

        $total_checked = StockBox::where('slip_no',$slip_no)->where('is_scanned', 1)->count();

        // dd($total_checkbox);

        $scanned_boxes = StockBox::where('slip_no',$slip_no)->where('is_scanned', 1)->get();

        $boxArr = array();

        if(!empty($scanned_boxes)){

            foreach($scanned_boxes as $item){

                $boxArr[] = $item->barcode_no;

            }

        }



        $order_id = $packingslip->order_id;

        $order = Order::find($order_id);

        $store_id = $order->store_id;

        $store = Store::find($store_id);

        // dd($store);

        return view('admin.packingslip.stockout', compact('id','slip_no','packingslip','search','data','totalData','total_checkbox','total_checked','boxArr','store'));

    }



    public function save_goods_out(Request $request)

    {        

        $request->validate([

            'slip_no' => 'required',

            'barcode.*' => 'required'

        ]);

        $params = $request->except('_token');        

        



        StockBox::where('packingslip_id', $params['packingslip_id'])->update([

            'is_stock_out' => 1

        ]);

        PackingslipNew1::where('id',$params['packingslip_id'])->update([

            'is_disbursed' => 1,

            'disbursed_by' => Auth::user()->id,

            'disbursed_at' => date('Y-m-d H:i:s')

        ]);



        $this->savePackingslipBarcodes($params['packingslip_id']);



        Session::flash('message', 'Goods disbursed successfully from packing slip  '.$params['slip_no']);

        return redirect()->route('admin.packingslip.order_in_progress');





    }



    private function savePackingslipBarcodes($packingslip_id){



        $packingslip = PackingslipNew1::find($packingslip_id);

        $order_id = $packingslip->order_id;

        $stock_products = StockBox::select('id','product_id')->selectRaw("SUM(pcs) AS total_item_pcs,COUNT(product_id) AS total_item_ctns")->where('packingslip_id', $packingslip_id)->groupBy('product_id')->get();



        foreach ($stock_products as $prod) {

            # Update Final Packing Slip Product Total Pcs...



            Packingslip::where('packingslip_id',$packingslip_id)->where('product_id', $prod->product_id)->update([

                'pcs' => $prod->total_item_pcs

            ]);



           # Stock Log Entry



        



            $getOrderProductDetails = getOrderProductDetails($order_id,$prod->product_id);

            $piece_price = $getOrderProductDetails->piece_price;

            $carton_price = $getOrderProductDetails->price;

            $total_price = ($piece_price * $prod->total_item_pcs);





            StockLog::insert([

                'entry_date' => date('Y-m-d'),

                'packingslip_id' => $packingslip_id,

                'product_id' => $prod->product_id,

                'quantity' => $prod->total_item_ctns,

                'pcs' => $prod->total_item_pcs,

                'type' => 'out',

                'entry_type' => 'ps',

                'piece_price' => $piece_price,

                'carton_price' => $carton_price,

                'total_price' => $total_price,

                'created_at' => date('Y-m-d H:i:s'),

                'updated_at' => date('Y-m-d H:i:s')

            ]);

        





        }

       

    }



    # Ajax

    public function checkScannedboxes(Request $request)

    {

        # code...

        $data = array();

        $slip_no = $request->slip_no;

        $packingslip_id = $request->packingslip_id;

        

        $data = StockBox::where('packingslip_id',$packingslip_id)->where('is_scanned', 1)->get();

        $count_pro_scanned = StockBox::select('product_id')->selectRaw("COUNT(id) AS total_scanned")->where('packingslip_id',$packingslip_id)->where(function($q){

            $q->where('is_scanned', 1);

        })->groupBy('product_id')->get();



        return response()->json(array('successData'=>$data,'count_pro_scanned'=>$count_pro_scanned));

        

        // return $data;

    }



    private function resetInvoicePayments($store_id,$collection_data)

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

                $invoice = Invoice::where('store_id',$store_id)->where('is_paid', 0)->orderBy('id','asc')->get();

                

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

