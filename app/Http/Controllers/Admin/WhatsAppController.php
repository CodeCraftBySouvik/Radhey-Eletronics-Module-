<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use App\Models\Invoice;
use App\Models\Store;
use App\Models\Ledger;
use App\Models\PackingslipNew1;
use App\Models\WhatsAppInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File; 
use Illuminate\Support\Str;
use Carbon\Carbon;

class WhatsAppController extends Controller
{
   public function invoice_list(Request $request){
        # all...
        $paginate = 20;
        $status = !empty($request->status)?$request->status:'';
        $term = !empty($request->term)?$request->term:'';
        $type = !empty($request->type)?$request->type:'';
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $store_name = !empty($request->store_name)?$request->store_name:'';
        $data = WhatsAppInvoice::with('order:id,order_no')->with('store:id,store_name,bussiness_name,whatsapp');
        $total = WhatsAppInvoice::select();
        

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
        if(!empty($status)){
            if($status == 'send'){
                $data = $data->where('status', 2);
                $total = $total->where('status', 2);
            }elseif($status == 'pending'){
                $data = $data->where('status', 1);
                $total = $total->where('status', 1);
            }elseif($status == 'cancelled'){
                $data = $data->where('status', 3);
                $total = $total->where('status', 3);
            }else{
                $data = $data->whereIn('status',[1,2,3]);
                $total = $total->whereIn('status',[1,2,3]);
            }
        }

        $total = $total->count();
        $data = $data->orderBy('id','desc')->paginate($paginate);
        $data = $data->appends([
            'term'=>$term,
            'page'=>$request->page,
            'type'=>$type,
            'store_id' => $store_id,
            'store_name' => $store_name
        ]);
        return view('admin.whatsapp.index', compact('data','term','type','total','paginate','store_id','store_name'));
   }
   public function upload_tally_bill(Request $request){
        $upload_path = "public/uploads/tally-bill/";
        $tally_file = $request->tally_file;     
        $bussiness_name = Str::slug($request->bussiness_name, '-');
	    $WhatsAppInvoice = WhatsAppInvoice::findOrFail($request->id);
        $TallyName = 'Tally-bill-'.$bussiness_name."-".$WhatsAppInvoice->id.".".$tally_file->getClientOriginalExtension();
        $tally_file->move($upload_path, $TallyName);
        $uploadedTally = $TallyName;
        $tally_file= 'uploads/tally-bill/'.$uploadedTally;
        

        $old_tally_file = !empty($WhatsAppInvoice->tally_bill_file)?$WhatsAppInvoice->tally_bill_file:'';

        if(!empty($old_tally_file)){
            $file_path = public_path().'/'.$old_tally_file;
            File::delete($file_path);
        }
        $WhatsAppInvoice->tally_bill_file = $tally_file;
        $WhatsAppInvoice->save();
        if($WhatsAppInvoice){
            return response()->json(['status'=>200]);
        }else{
            return response()->json(['status'=>400]);
        }
   }
   public function tally_bill_not_required(Request $request){
        $WhatsAppInvoice = WhatsAppInvoice::findOrFail($request->id);
        $WhatsAppInvoice->tb_required = $request->status;
        if($request->status==0){
            $old_tally_file = !empty($WhatsAppInvoice->tally_bill_file)?$WhatsAppInvoice->tally_bill_file:'';
            if(!empty($old_tally_file)){
                $file_path = public_path().'/'.$old_tally_file;
                File::delete($file_path);
                $WhatsAppInvoice->tally_bill_file = NULL;
            }
        }
        $WhatsAppInvoice->save();
        return response()->json(200);
   }
   public function upload_lr_bill(Request $request){
        $upload_path = "public/uploads/lr-bill/";
        $lr_file = $request->lr_file;
        $bussiness_name = Str::slug($request->bussiness_name, '-');
        $WhatsAppInvoice = WhatsAppInvoice::findOrFail($request->id);
        $TallyName = 'Transport-LR-'.$bussiness_name."-".$WhatsAppInvoice->id.".".$lr_file->getClientOriginalExtension();
        $lr_file->move($upload_path, $TallyName);
        $uploadedTally = $TallyName;
        $lr_file= 'uploads/lr-bill/'.$uploadedTally;
        

        $old_lr_file = !empty($WhatsAppInvoice->transport_lr_file)?$WhatsAppInvoice->transport_lr_file:'';

        if(!empty($old_lr_file)){
            $file_path = public_path().'/'.$old_lr_file;
            File::delete($file_path);
        }
        $WhatsAppInvoice->transport_lr_file = $lr_file;
        $WhatsAppInvoice->save();
        if($WhatsAppInvoice){
            return response()->json(['status'=>200]);
        }else{
            return response()->json(['status'=>400]);
        }
   }
   public function lr_bill_not_required(Request $request){
        $WhatsAppInvoice = WhatsAppInvoice::findOrFail($request->id);
        $WhatsAppInvoice->lr_required = $request->status;
        if($request->status==0){
            $old_transport_lr_file = !empty($WhatsAppInvoice->transport_lr_file)?$WhatsAppInvoice->transport_lr_file:'';
            if(!empty($old_transport_lr_file)){
                $file_path = public_path().'/'.$old_transport_lr_file;
                File::delete($file_path);
                $WhatsAppInvoice->transport_lr_file = NULL;
            }
        }
        $WhatsAppInvoice->save();
        return response()->json(200);
   }
   public function invoice_cancel($id){
    $WhatsAppInvoice = WhatsAppInvoice::findOrFail($id);
    $WhatsAppInvoice->status= 3;
    $WhatsAppInvoice->save();
    return redirect()->back();
   }
   public function invoice_active($id){
    $WhatsAppInvoice = WhatsAppInvoice::findOrFail($id);
    $WhatsAppInvoice->status= 1;
    $WhatsAppInvoice->save();
    return redirect()->back();
   }
   public function ledger_cancel($id){
    $WhatsAppInvoice = Ledger::findOrFail($id);
    $WhatsAppInvoice->whatsapp_status= 2;
    $WhatsAppInvoice->save();
    return redirect()->back();
   }
   public function ledger_active($id){
    $WhatsAppInvoice = Ledger::findOrFail($id);
    $WhatsAppInvoice->whatsapp_status= 0;
    $WhatsAppInvoice->save();
    return redirect()->back();
   }
    public function user_ledger(Request $request)
    {

        
        $designation = Auth::user()->designation;

        $auth_type = Auth::user()->type;

        if($auth_type == 2){

            $userAccesses = userAccesses($designation,11);

            if(!$userAccesses){

                abort(401);

            }

        }

        $user_type = !empty($request->user_type)?$request->user_type:'';

        $store_id = !empty($request->store_id)?$request->store_id:0;

        $staff_id = !empty($request->staff_id)?$request->staff_id:0;

        $admin_id = !empty($request->admin_id)?$request->admin_id:0;

        $supplier_id = !empty($request->supplier_id)?$request->supplier_id:0;

        $select_user_name = !empty($request->select_user_name)?$request->select_user_name:'';

        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-01', strtotime(date('Y-m-d')));

        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');

        

        if(Auth::user()->designation == NULL){

            $bank_cash = !empty($request->bank_cash)?$request->bank_cash:'';

        } else {

            $bank_cash = 'bank';

        }

        $sort_by = !empty($request->sort_by)?$request->sort_by:'asc';



        $data = $outstanding = array();

        $day_opening_amount = $is_opening_bal =  0;

        $non_tr_day_opening_amount = 0;

        $is_opening_bal_showable = 1;

        $opening_bal_date = "";

        

        $store = DB::table('stores')->select('id','store_name AS name')->where('status',1)->get();

        $staff = DB::table('users')->select('id','name')->where('status',1)->get();



        $isTransactionFound = false;

        if(!empty($user_type)){



            if(!empty($store_id) || !empty($supplier_id) || !empty($staff_id) || !empty($admin_id)){

                $isTransactionFound = true;

                DB::enableQueryLog();

                $data = DB::table('ledger AS l')->select('l.*','p.voucher_no','p.payment_in','p.amount AS payment_amount','p.payment_mode','p.chq_utr_no','p.narration');

                

                $opening_bal = DB::table('ledger');



                if($user_type == 'store' && !empty($store_id)){

                    $data = $data->where('l.user_type', 'store')->where('l.store_id',$store_id);

                    

                    $opening_bal = $opening_bal->where('user_type',$user_type)->where('store_id',$store_id);

                }else if($user_type == 'staff'  && !empty($staff_id)){

                    $data = $data->where('l.user_type', 'staff')->where('l.staff_id',$staff_id);



                    $notCommData = DB::table('ledger')->where('user_type', 'staff')->where('staff_id',$staff_id)->whereRaw("(DATE_FORMAT(entry_date, '%Y-%m') < '2023-10' AND purpose = 'payment_collection_commission'  )")->pluck('id')->toArray();

                        

                    $opening_bal = $opening_bal->where('user_type',$user_type)->where('staff_id',$staff_id);

                    

                    if(!empty($notCommData)){

                        // dd($notCommData);

                        $data = $data->whereNotIn('l.id',$notCommData);

                        $opening_bal = $opening_bal->whereNotIn('id',$notCommData);

                    }



                }else if($user_type == 'partner' && !empty($admin_id)){

                    $data = $data->where('l.user_type', 'partner')->where('l.admin_id',$admin_id);

                    

                    $opening_bal = $opening_bal->where('user_type',$user_type)->where('admin_id',$admin_id);

                }else if($user_type == 'supplier' && !empty($supplier_id)){

                    $data = $data->where('l.user_type','supplier')->where('l.supplier_id',$supplier_id);

                    $opening_bal = $opening_bal->where('user_type',$user_type)->where('supplier_id',$supplier_id);

                }



                $check_ob_exist_store = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'store')->where('store_id',$store_id)->orderBy('id','asc')->first();



                if(!empty($check_ob_exist_store)){

                    $from_date = ($request->from_date < $check_ob_exist_store->entry_date) ? $check_ob_exist_store->entry_date : $request->from_date;

                    $is_opening_bal = 1;

                    $opening_bal_date = $check_ob_exist_store->entry_date;


                    if($from_date == $check_ob_exist_store->entry_date){                    

                        $is_opening_bal_showable = 0;                    

                    } else {

                        $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_store->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");

                    }                

                    

                } else {

                    $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");

                } 



                /* +++++++++++++++++++ */



                $check_ob_exist_partner = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'partner')->where('admin_id',$admin_id)->orderBy('id','asc')->first();



                if(!empty($check_ob_exist_partner)){

                    $from_date = ($request->from_date < $check_ob_exist_partner->entry_date) ? $check_ob_exist_partner->entry_date : $request->from_date;

                    $is_opening_bal = 1;

                    $opening_bal_date = $check_ob_exist_partner->entry_date;



                    if($from_date == $check_ob_exist_partner->entry_date){                    

                        $is_opening_bal_showable = 0;                    

                    } else {

                        $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_partner->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");

                    }                

                    

                } else {

                    // die('Hi');

                    $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");

                } 

                

                if(!empty($from_date) && !empty($to_date)){

                    $data = $data->whereRaw("l.entry_date BETWEEN '".$from_date."' AND '".$to_date."' ");

                }

                

                if(Auth::user()->type == 2){

                    $opening_bal = $opening_bal->where('is_gst', 1);

                }

                $opening_bal = $opening_bal->orderBy('entry_date',$sort_by);  

                $opening_bal = $opening_bal->orderBy('updated_at',$sort_by);  

                $opening_bal = $opening_bal->get();



                foreach($opening_bal as $ob){

                    if(!empty($ob->is_credit)){

                        $credit_amount = $ob->transaction_amount;

                        $day_opening_amount += $ob->transaction_amount;

                    }

                    if(!empty($ob->is_debit)){

                        $debit_amount = $ob->transaction_amount;

                        $day_opening_amount -= $ob->transaction_amount;

                    }

                }
                if(!empty($bank_cash)){

                    $data = $data->where('l.bank_cash', $bank_cash);

                }
                $data = $data->leftJoin('payment AS p','p.id','l.payment_id');

                $data = $data->orderBy('l.entry_date',$sort_by);  

                $data = $data->orderBy('l.updated_at',$sort_by);  

                $data = $data->get()->toarray();  
                if(empty($data)){

                    // dd('Empty');

                    $non_tr_opening_bal = DB::table('ledger AS l');

                    if($user_type == 'store' && !empty($store_id)){

                        $non_tr_opening_bal = $non_tr_opening_bal->where('l.user_type', 'store')->where('l.store_id',$store_id);

                        

                        $started_date = DB::table('ledger')->where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');

                    }else if($user_type == 'staff'  && !empty($staff_id)){

                        $non_tr_opening_bal = $non_tr_opening_bal->where('l.user_type', 'staff')->where('l.staff_id',$staff_id);

                        

                        $started_date = DB::table('ledger')->where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');

                    }else if($user_type == 'partner' && !empty($admin_id)){

                        $non_tr_opening_bal = $non_tr_opening_bal->where('l.user_type', 'partner')->where('l.admin_id',$admin_id);

                        

                        $started_date = DB::table('ledger')->where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');

                    }else if($user_type == 'supplier' && !empty($supplier_id)){

                        $non_tr_opening_bal = $non_tr_opening_bal->where('l.user_type','supplier')->where('l.supplier_id',$supplier_id);

                        $started_date = DB::table('ledger')->where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');

                    }

                    if(!empty($bank_cash)){

                        $non_tr_opening_bal = $non_tr_opening_bal->where('bank_cash', $bank_cash);

                    }
                    $non_tr_opening_bal = $non_tr_opening_bal->whereRaw("l.entry_date BETWEEN '".$started_date."' AND '".$to_date."' ")->get();



                    // dd($non_tr_opening_bal);

                    if(!empty($non_tr_opening_bal)){

                        foreach($non_tr_opening_bal as $bal){

                            if(!empty($bal->is_credit)){

                                $credit_amount = $bal->transaction_amount;

                                $non_tr_day_opening_amount += $bal->transaction_amount;

                            }

                            if(!empty($bal->is_debit)){

                                $debit_amount = $bal->transaction_amount;

                                $non_tr_day_opening_amount -= $bal->transaction_amount;

                            }

                        }

                    }

                    

                }

            } else {

                $isTransactionFound = false;

            }
        }
        return view('admin.whatsapp.ledger', compact('store','staff','data','user_type','store_id','staff_id','admin_id','supplier_id','select_user_name','from_date','to_date','sort_by','day_opening_amount','is_opening_bal','is_opening_bal_showable','opening_bal_date','bank_cash','non_tr_day_opening_amount','isTransactionFound'));

    }

    public function ledger_list(Request $request){
        $designation = Auth::user()->designation;
        $auth_type = Auth::user()->type;
        // if($auth_type == 2){
        //     $userAccesses = userAccesses($designation,11);
        //     if(!$userAccesses){
        //         abort(401);
        //     }
        // }
        $store = DB::table('stores')->select('id','store_name AS name')->where('status',1)->get();

        $staff = DB::table('users')->select('id','name')->where('status',1)->get();

        $user_type = !empty($request->user_type)?$request->user_type:0;

        $store_id = !empty($request->store_id)?$request->store_id:0;

        $staff_id = !empty($request->staff_id)?$request->staff_id:0;

        $admin_id = !empty($request->admin_id)?$request->admin_id:0;

        $supplier_id = !empty($request->supplier_id)?$request->supplier_id:0;

        $select_user_name = !empty($request->select_user_name)?$request->select_user_name:'';

        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-01', strtotime(date('Y-m-d')));

        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');


        if($store_id>0){
            // Your code for a single store_id query
            $data = DB::table('ledger')
                ->select('ledger.is_credit', 'ledger.start_date', 'ledger.store_id', 'ledger.last_whatsapp', 'stores.bussiness_name', 'stores.whatsapp', 'ledger.id', 'ledger.whatsapp_status', 'ledger.created_at AS last_date')
                ->selectSub(function ($query) use ($store_id) {
                    $query->select('created_at')
                        ->from('ledger as l2')
                        ->whereColumn('l2.store_id', 'ledger.store_id')
                        ->orderBy('l2.id')
                        ->limit(1);
                }, 'first_date')
                ->join('stores', 'stores.id', '=', 'ledger.store_id')
                ->whereIn('ledger.id', function($query) {
                    $query->select(DB::raw('MAX(id)'))
                            ->from('ledger')
                            ->groupBy('store_id');
                })
                ->where('ledger.store_id', $store_id)
                ->where('ledger.is_credit', 1)
                ->where('ledger.created_at', '>=', '2024-05-14 00:00:00')
                ->orderBy('ledger.id', 'desc')->limit(1)
                ->paginate(1);
        }else{
                $data = DB::table('ledger')
                ->select('ledger.is_credit', 'ledger.start_date', 'ledger.store_id', 'ledger.last_whatsapp', 'stores.bussiness_name', 'stores.whatsapp', 'ledger.id', 'ledger.whatsapp_status', 'ledger.created_at AS last_date')
                ->selectSub(function ($query) {
                    $query->select('created_at')
                            ->from('ledger as l2')
                            ->whereColumn('l2.store_id', 'ledger.store_id')
                            ->orderBy('l2.id')
                            ->limit(1);
                }, 'first_date')
                ->join('stores', 'stores.id', '=', 'ledger.store_id')
                ->whereNotNull('ledger.store_id')
                ->whereIn('ledger.id', function($query) {
                    $query->select(DB::raw('MAX(id)'))
                            ->from('ledger')
                            ->groupBy('store_id');
                })
                ->where('ledger.is_credit', 1)
                ->where('ledger.created_at', '>=', '2024-05-14 00:00:00')
                ->orderBy('ledger.id', 'desc')
                ->paginate(25);
            }
        

        return view('admin.whatsapp.ledger_list', compact('store','staff','data','user_type','store_id','staff_id','admin_id','supplier_id','select_user_name','from_date','to_date'));
    }
   public function send_text_whatsapp_message($id, $mobile){
        // $mobile = 9674030434;
        // $mobile = 8617207525;
       $WhatsAppInvoice = WhatsAppInvoice::where('id', $id)->first();
       if ($WhatsAppInvoice) {
           if (isValidMobileNumber($mobile)) {
                $invoice = Invoice::with('order', 'store', 'user', 'products')->where('invoice_no', $WhatsAppInvoice->invoice_no)->first();

                if (!$invoice) {
                    Session::flash('message', 'Invoice data not found!');
                    return redirect()->route('admin.whats-app.invoice_list');
                }
                // Generate PDF for the invoice
                if (!empty($invoice->is_gst)) {
                    $pdf = PDF::loadView('admin.packingslip.invoice', compact('invoice'));
                } else {
                    $pdf = PDF::loadView('admin.packingslip.cashslip', compact('invoice'));
                }

                $upload_path = public_path('uploads/whatsapp/invoice/');
                $bussiness_name = $invoice->store?$invoice->store->bussiness_name:date('d-m-Y');
                $bussiness_name = Str::slug($bussiness_name, '-');
                // Generate a unique filename using the invoice number
                $invoice_pdf_filename = 'Estimate-slip-'.$bussiness_name . '.pdf';

                // Check if the directory exists, if not, create it
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                // Save the PDF content to the specified path
                if ($pdf->save($upload_path . $invoice_pdf_filename)) {
                    // Construct the full path of the saved PDF file
                    $final_invoice_pdf_path = asset('uploads/whatsapp/invoice/' . $invoice_pdf_filename);
                } else {
                    // If saving fails, set $final_invoice_pdf_path to null
                    $final_invoice_pdf_path = null;
                }
            
            // Fetch the packingslip
                $packingslip = PackingslipNew1::where('id', $WhatsAppInvoice->packingslip_id)->first();
                $data = DB::table('packing_slip AS ps')->select('ps.*', 'p.name AS pro_name', 'o.order_no', 'o.created_at AS ordered_at', 's.store_name', 's.whatsapp AS store_whatsapp', 's.bussiness_name')->leftJoin('orders AS o', 'o.id', 'ps.order_id')->leftJoin('products AS p', 'p.id', 'ps.product_id')->leftJoin('stores AS s', 's.id', 'o.store_id')->where('ps.slip_no', $packingslip->slipno)->get();

                // Generate PDF for the packing slip
                $packingslip_pdf = PDF::loadView('admin.packingslip.pdf', compact('data', 'packingslip'))->output();

                // Define the upload directory
                $upload_path = public_path('uploads/whatsapp/packingslip/');

                // Generate a unique filename using the packing slip ID
                $packingslip_pdf_filename = 'Packing-slip-' . $bussiness_name . '.pdf';

                // Check if the directory exists, if not, create it
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                // Save the PDF content to the specified path
                file_put_contents($upload_path . $packingslip_pdf_filename, $packingslip_pdf);

                // Construct the full path of the saved PDF file
                $final_packingslip_pdf_path = asset('uploads/whatsapp/packingslip/' . $packingslip_pdf_filename);

                // Check if the PDF was saved successfully
                if (file_exists($upload_path . $packingslip_pdf_filename)) {
                $packingslip_pdf_filename = $packingslip_pdf_filename;
                } else {
                    $packingslip_pdf_filename = null;
                }

            // File links
            $fileLinks = [
                [
                    'name'=>"Invoice",
                    'url' => $final_invoice_pdf_path,
                ],
                [
                    'name'=>"Packingslip",
                    'url' => $final_packingslip_pdf_path,
                ],
                [
                        'name'=>"transport-LR",
                        'url' => $WhatsAppInvoice->transport_lr_file ? asset($WhatsAppInvoice->transport_lr_file) : null,
                    ],
                [
                        'name'=>"tally-bill",
                        'url' => $WhatsAppInvoice->tally_bill_file ? asset($WhatsAppInvoice->tally_bill_file) : null,
                ],
            ];
                $mobile ='91'.$mobile;
               // Get the token from the environment configuration
               $token = env('WHATSAPP_TOKEN_VARIABLE');
                // $sending_file = [];
                // dd($fileLinks);
                foreach ($fileLinks as $fileLink) {
                    if($fileLink['url']!=null){
                        try {
                            // $sending_file[]= $fileLink['name'];
                            // Initialize cURL session
                            $ch = curl_init();
                            $link = $fileLink['url'];
                            // Set the URL
                            $url = "https://transapi.bluwaves.in/api/sendFiles?token=$token&phone=$mobile&link=$link";
                                // Initialize cURL session
                            $ch = curl_init($url);
                            // Set cURL options
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            $response = curl_exec($ch);
                            // dd($response);
                            // Check for errors
                            if ($response === false) {
                                // Handle error
                                $error = curl_error($ch);
                                $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                    'response' => json_encode($response),
                                    'mobile'=>$mobile,
                                    'sending_file'=>$fileLink['name'],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                curl_close($ch);
                            } else {
                                $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                    'response' => json_encode($response),
                                    'mobile'=>$mobile,
                                    'sending_file'=>$fileLink['name'],
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                curl_close($ch);
                            }
                        } catch (\Exception $e) {
                            // Handle exception
                            $errorMessage = $e->getMessage();
                            $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                'response' => json_encode($errorMessage),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
               }
               $whatsapp_invoices = DB::table('whatsapp_invoices')
               ->where('id', $id)
               ->update(['last_whatsapp' => now(), 'status'=>2]);
               Session::flash('message', 'Message sent successfully!');
               return redirect()->route('admin.whats-app.invoice_list');
           } else {
               // If mobile number is not valid, return false
               Session::flash('message', 'Invalid mobile number ' . $mobile);
               return redirect()->route('admin.whats-app.invoice_list');
           }
       } else {
           Session::flash('message', 'Invoice data not found!');
           return redirect()->route('admin.whats-app.invoice_list');
       }
   }
   public function send_ledger_text_whatsapp_message(Request $request){
        $mobile = $request->whatsapp;
        // $mobile = 8016638037;
        // $mobile = 9674030434;
        $token = env('WHATSAPP_TOKEN_VARIABLE');
        if (isValidMobileNumber($mobile)) {

            $user_type = !empty($request->user_type)?$request->user_type:'';

            $store_id = !empty($request->store_id)?$request->store_id:0;

            $staff_id = !empty($request->staff_id)?$request->staff_id:0;

            $admin_id = !empty($request->admin_id)?$request->admin_id:0;

            $supplier_id = !empty($request->supplier_id)?$request->supplier_id:0;

            $select_user_name = !empty($request->select_user_name)?$request->select_user_name:'';

            $from_date = !empty($request->from_date)?$request->from_date:'';
            // $to_date = !empty($request->to_date)?$request->to_date:'';
            // $year = date('Y');
            // $april_1 = $year . '-04-01';
            // $from_date = $april_1;
            // $from_date = $item->first_date;
            $to_date = date('Y-m-d');
            $store_data = Store::findOrFail($store_id);
            $store_bussiness_name = $store_data->bussiness_name?$store_data->bussiness_name:$store_data->store_name;
            $sort_by = !empty($request->sort_by)?$request->sort_by:'asc';

            if(Auth::user()->designation == NULL){

                $bank_cash = !empty($request->bank_cash)?$request->bank_cash:'';

            } else {
                // $bank_cash = 'bank';
                $bank_cash = '';

            }


            $data = $outstanding = array();

            $day_opening_amount = $is_opening_bal =  0;

            $is_opening_bal_showable = 1;

            $opening_bal_date = "";

            // dd($data);
            if(!empty($user_type)){

                DB::enableQueryLog();

                $data = DB::table('ledger AS l')->select('l.*','p.voucher_no','p.payment_in','p.amount AS payment_amount','p.payment_mode','p.chq_utr_no','p.narration');

                

                $opening_bal = DB::table('ledger');



                if($user_type == 'store' && !empty($store_id)){

                    $data = $data->where('l.user_type', 'store')->where('l.store_id',$store_id);

                    

                    $opening_bal = $opening_bal->where('user_type',$user_type)->where('store_id',$store_id);

                }
                

                $check_ob_exist_store = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'store')->where('store_id',$store_id)->orderBy('id','asc')->first();



                if(!empty($check_ob_exist_store)){

                    $from_date = ($request->from_date < $check_ob_exist_store->entry_date) ? $check_ob_exist_store->entry_date : $request->from_date;

                    $is_opening_bal = 1;

                    $opening_bal_date = $check_ob_exist_store->entry_date;



                    if($from_date == $check_ob_exist_store->entry_date){                    

                        $is_opening_bal_showable = 0;                    

                    } else {

                        $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_store->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");

                    }                

                    

                } else {

                    // die('Hi');

                    $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");

                } 



                /* +++++++++++++++++++ */



                $check_ob_exist_partner = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'partner')->where('admin_id',$admin_id)->orderBy('id','asc')->first();



                if(!empty($check_ob_exist_partner)){

                    $from_date = ($request->from_date < $check_ob_exist_partner->entry_date) ? $check_ob_exist_partner->entry_date : $request->from_date;

                    $is_opening_bal = 1;

                    $opening_bal_date = $check_ob_exist_partner->entry_date;



                    if($from_date == $check_ob_exist_partner->entry_date){                    

                        $is_opening_bal_showable = 0;                    

                    } else {

                        $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_partner->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");

                    }                

                    

                } else {

                    // die('Hi');

                    $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");

                } 



                

                if(!empty($from_date) && !empty($to_date)){

                    $data = $data->whereRaw("l.entry_date BETWEEN '".$from_date."' AND '".$to_date."' ");

                }



                if(Auth::user()->type == 2){

                    $opening_bal = $opening_bal->where('is_gst', 1);

                }

                

                $opening_bal = $opening_bal->orderBy('entry_date',$sort_by);  

                $opening_bal = $opening_bal->orderBy('updated_at',$sort_by);  

                $opening_bal = $opening_bal->get();
                foreach($opening_bal as $ob){

                    if(!empty($ob->is_credit)){

                        $credit_amount = $ob->transaction_amount;

                        $day_opening_amount += $ob->transaction_amount;

                    }

                    if(!empty($ob->is_debit)){

                        $debit_amount = $ob->transaction_amount;

                        $day_opening_amount -= $ob->transaction_amount;

                    }

                }



                if(!empty($bank_cash)){

                    $data = $data->where('l.bank_cash', $bank_cash);

                }



                // dd($day_opening_amount);

                $data = $data->leftJoin('payment AS p','p.id','l.payment_id');

                $data = $data->orderBy('l.entry_date',$sort_by);  

                $data = $data->orderBy('l.updated_at',$sort_by);  

                $data = $data->get()->toarray(); 
                
            }
            $ledgerpdfname = ucwords($user_type)."-".date('Y-m-d-H-i-s-A')."";
            $pdf = Pdf::loadView('admin.report.ledger-pdf', compact('data','user_type','store_id','staff_id','admin_id','supplier_id','select_user_name','from_date','to_date','day_opening_amount','is_opening_bal','is_opening_bal_showable','opening_bal_date','bank_cash'));
            // dd($request->ledger_id);
            if (isset($request->ledger_id) && !empty($request->ledger_id)) {
                $upload_path = public_path('uploads/whatsapp/ledger/');

                // Generate a unique filename using the invoice number
                $store_bussiness_name = Str::slug($store_bussiness_name, '-');
                $ledger_pdf_filename = 'Ledger-'.$store_bussiness_name. '.pdf';

                // Check if the directory exists, if not, create it
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                // Save the PDF content to the specified path
                if ($pdf->save($upload_path . $ledger_pdf_filename)) {
                    // Construct the full path of the saved PDF file
                    $ledger_pdf_file_path = asset('uploads/whatsapp/ledger/' . $ledger_pdf_filename);
                } else {
                    // If saving fails, set $final_invoice_pdf_path to null
                    $ledger_pdf_file_path = null;
                }

                if($ledger_pdf_file_path!=null){
                    try {
                        $mobile = '91'.$mobile;
                        $ch = curl_init();
                        $link = $ledger_pdf_file_path;
                        // Set the URL
                        $url = "https://transapi.bluwaves.in/api/sendFiles?token=$token&phone=$mobile&link=$link";
                            // Initialize cURL session
                        $ch = curl_init($url);
                        // Set cURL options
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        // Check for errors
                        if ($response === false) {
                            // Handle error
                            $error = curl_error($ch);
                            $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                'response' => json_encode($response),
                                'mobile'=>$mobile,
                                'sending_file'=>"Ledger",
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            curl_close($ch);
                        } else {
                            $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                                'response' => json_encode($response),
                                'mobile'=>$mobile,
                                'sending_file'=>"Ledger",
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $ledger = DB::table('ledger')
                            ->where('id', $request->ledger_id)
                            ->update(['last_whatsapp' => now(), 'whatsapp_status'=>1]);
                            curl_close($ch);
                        }
                    } catch (\Exception $e) {
                        // Handle exception
                        $errorMessage = $e->getMessage();
                        $whatsapp_message_logs = DB::table('whatsapp_message_logs')->insert([
                            'response' => json_encode($errorMessage),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
                Session::flash('message', 'Message sent successfully!');
                return redirect()->route('admin.whatsapp_ledger_user'); 
            }else{
                Session::flash('message', 'Ledger data not found!');
                return redirect()->route('admin.whatsapp_ledger_user'); 
            }
        } else {
            // If mobile number is not valid, return false
            Session::flash('message', 'Invalid mobile number ' . $mobile);
            return redirect()->route('admin.whatsapp_ledger_user');
        }
   }
   public function getLeftWhatsappCounter($id){
        $WhatsAppInvoice = WhatsAppInvoice::where('id', $id)->first();
        // if($WhatsAppInvoice->tb_required==0 && $WhatsAppInvoice->lr_required==0){
        //     $updated_at = $WhatsAppInvoice->updated_at;
        // }elseif($WhatsAppInvoice->tb_required==1 && $WhatsAppInvoice->lr_required==1 && isset($WhatsAppInvoice->tally_bill_file) && isset($WhatsAppInvoice->transport_lr_file)){
        //     $updated_at = $WhatsAppInvoice->updated_at;
        // }else{
        //     // $updated_at = Carbon::parse($WhatsAppInvoice->updated_at)->timezone("Asia/Kolkata");
        //     // // Add 90 minutes to the end datetime
        //     // $updated_at->addMinutes(10);
        //     $updated_at = 0;
        // }
        if ($WhatsAppInvoice->tally_bill_file && $WhatsAppInvoice->transport_lr_file) {
            $updated_at = $WhatsAppInvoice->updated_at;
        } elseif (
            ($WhatsAppInvoice->tb_required == 0 && $WhatsAppInvoice->lr_required == 0) || 
            ($WhatsAppInvoice->tb_required == 0 && isset($WhatsAppInvoice->transport_lr_file)) || 
            ($WhatsAppInvoice->lr_required == 0 && isset($WhatsAppInvoice->tally_bill_file))
        ) {
            $updated_at = $WhatsAppInvoice->updated_at;
        } else {
            $updated_at = 0;
        }
        // Parse the provided $updated_at datetime string into a Carbon instance
        $endDateTime = Carbon::parse($updated_at)->timezone("Asia/Kolkata");
        // Add 90 minutes to the end datetime
        $endDateTime->addMinutes(90);

        // Get the current datetime
        $startDateTime = Carbon::now()->timezone("Asia/Kolkata");

        // Check if the end datetime is in the past
        if ($endDateTime < $startDateTime) {
            $response = "Date Expired";
            return response()->json(['counter' => $response]);
        }

        // Calculate the remaining time until the end datetime
        $endRemainingTime = $endDateTime->diff($startDateTime);

        // Format the remaining time into days, hours, minutes, and seconds
        $days = $endRemainingTime->d;
        $hours = $endRemainingTime->h;
        $minutes = $endRemainingTime->i;
        $seconds = $endRemainingTime->s;

        // Return the formatted remaining time
        $response = "Whatsapp Message: $hours h $minutes m $seconds s Left";
        return response()->json(['counter' => $response]);
   }
   public function getLedgerLeftWhatsappCounter($id){
        $ledger = Ledger::findOrFail($id);
        $endDateTime = Carbon::parse($ledger->created_at)->timezone("Asia/Kolkata");
        // Add 90 minutes to the end datetime
        $endDateTime->addMinutes(90);

        // Get the current datetime
        $startDateTime = Carbon::now()->timezone("Asia/Kolkata");

        // Check if the end datetime is in the past
        if ($endDateTime < $startDateTime) {
            $response = "Date Expired";
            return response()->json(['counter' => $response]);
        }

        // Calculate the remaining time until the end datetime
        $endRemainingTime = $endDateTime->diff($startDateTime);

        // Format the remaining time into days, hours, minutes, and seconds
        $days = $endRemainingTime->d;
        $hours = $endRemainingTime->h;
        $minutes = $endRemainingTime->i;
        $seconds = $endRemainingTime->s;

        // Return the formatted remaining time
        $response = "Whatsapp Message: $hours h $minutes m $seconds s Left";
        return response()->json(['counter' => $response]);
   }
   public function update_ledger_start_date(Request $request){
        try {
            // Find the Ledger record by its ID
            $ledger = Ledger::findOrFail($request->id);
            // Update the start_date attribute
            $ledger->start_date = $request->start_date;
            
            // Save the changes to the database
            $ledger->save();

            // Return a success response
            Session::flash('message', 'Start date updated successfully!');
            return response()->json(['success' => true, 'message' => 'Start date updated successfully']);
        } catch (\Exception $e) {
            // If an exception occurs, return an error response
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
   }
   public function whatsapp_user_ledger_pdf(Request $request)
    {
        $user_type = !empty($request->user_type)?$request->user_type:'';
        $store_id = !empty($request->store_id)?$request->store_id:0;
        $staff_id = !empty($request->staff_id)?$request->staff_id:0;
        $admin_id = !empty($request->admin_id)?$request->admin_id:0;
        $supplier_id = !empty($request->supplier_id)?$request->supplier_id:0;
        $select_user_name = !empty($request->select_user_name)?$request->select_user_name:'';
        $from_date = !empty($request->from_date)?$request->from_date:'';
        $to_date = !empty($request->to_date)?$request->to_date:'';
        $sort_by = !empty($request->sort_by)?$request->sort_by:'asc';

        if(Auth::user()->designation == NULL){
            $bank_cash = !empty($request->bank_cash)?$request->bank_cash:'';
        } else {
            // $bank_cash = 'bank';
            $bank_cash = '';
        }

        $data = $outstanding = array();
        $day_opening_amount = $is_opening_bal =  0;
        $is_opening_bal_showable = 1;
        $opening_bal_date = "";
        
        
        if(!empty($user_type)){
            
            DB::enableQueryLog();
            $data = DB::table('ledger AS l')->select('l.*','p.voucher_no','p.payment_in','p.amount AS payment_amount','p.payment_mode','p.chq_utr_no','p.narration');
            
            $opening_bal = DB::table('ledger');

            if($user_type == 'store' && !empty($store_id)){
                $data = $data->where('l.user_type', 'store')->where('l.store_id',$store_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('store_id',$store_id);
            }else if($user_type == 'staff'  && !empty($staff_id)){
                $data = $data->where('l.user_type', 'staff')->where('l.staff_id',$staff_id);

                $notCommData = DB::table('ledger')->where('user_type', 'staff')->where('staff_id',$staff_id)->whereRaw("(DATE_FORMAT(entry_date, '%Y-%m') < '2023-10' AND purpose = 'payment_collection_commission'  )")->pluck('id')->toArray();                
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('staff_id',$staff_id);

                if(!empty($notCommData)){
                    // dd($notCommData);
                    $data = $data->whereNotIn('l.id',$notCommData);
                    $opening_bal = $opening_bal->whereNotIn('id',$notCommData);
                }

            }else if($user_type == 'partner' && !empty($admin_id)){
                $data = $data->where('l.user_type', 'partner')->where('l.admin_id',$admin_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('admin_id',$admin_id);
            }else if($user_type == 'supplier' && !empty($supplier_id)){
                $data = $data->where('l.user_type','supplier')->where('l.supplier_id',$supplier_id);
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('supplier_id',$supplier_id);
            }

            $check_ob_exist_store = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'store')->where('store_id',$store_id)->orderBy('id','asc')->first();

            if(!empty($check_ob_exist_store)){
                $from_date = ($request->from_date < $check_ob_exist_store->entry_date) ? $check_ob_exist_store->entry_date : $request->from_date;
                $is_opening_bal = 1;
                $opening_bal_date = $check_ob_exist_store->entry_date;

                if($from_date == $check_ob_exist_store->entry_date){                    
                    $is_opening_bal_showable = 0;                    
                } else {
                    $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_store->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                }                
                
            } else {
                // die('Hi');
                $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
            } 

            /* +++++++++++++++++++ */

            $check_ob_exist_partner = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'partner')->where('admin_id',$admin_id)->orderBy('id','asc')->first();

            if(!empty($check_ob_exist_partner)){
                $from_date = ($request->from_date < $check_ob_exist_partner->entry_date) ? $check_ob_exist_partner->entry_date : $request->from_date;
                $is_opening_bal = 1;
                $opening_bal_date = $check_ob_exist_partner->entry_date;

                if($from_date == $check_ob_exist_partner->entry_date){                    
                    $is_opening_bal_showable = 0;                    
                } else {
                    $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_partner->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                }                
                
            } else {
                // die('Hi');
                $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
            } 

              
            if(!empty($from_date) && !empty($to_date)){
                $data = $data->whereRaw("l.entry_date BETWEEN '".$from_date."' AND '".$to_date."' ");
            }

            if(Auth::user()->type == 2){
                $opening_bal = $opening_bal->where('is_gst', 1);
            }
            
            $opening_bal = $opening_bal->orderBy('entry_date',$sort_by);  
            $opening_bal = $opening_bal->orderBy('updated_at',$sort_by);  
            $opening_bal = $opening_bal->get();

            // dd($opening_bal);

            foreach($opening_bal as $ob){
                if(!empty($ob->is_credit)){
                    $credit_amount = $ob->transaction_amount;
                    $day_opening_amount += $ob->transaction_amount;
                }
                if(!empty($ob->is_debit)){
                    $debit_amount = $ob->transaction_amount;
                    $day_opening_amount -= $ob->transaction_amount;
                }
            }

            if(!empty($bank_cash)){
                $data = $data->where('l.bank_cash', $bank_cash);
            }

            // dd($day_opening_amount);
            $data = $data->leftJoin('payment AS p','p.id','l.payment_id');
            $data = $data->orderBy('l.entry_date',$sort_by);  
            $data = $data->orderBy('l.updated_at',$sort_by);  
            $data = $data->get()->toarray(); 
            
            // dd($data);

        }

        $ledgerpdfname = ucwords($user_type)."-".date('Y-m-d-H-i-s-A')."";

        $pdf = Pdf::loadView('admin.report.ledger-pdf', compact('data','user_type','store_id','staff_id','admin_id','supplier_id','select_user_name','from_date','to_date','day_opening_amount','is_opening_bal','is_opening_bal_showable','opening_bal_date','bank_cash'));
        return $pdf->download($ledgerpdfname.'.pdf');
        
        
    }
   
}
