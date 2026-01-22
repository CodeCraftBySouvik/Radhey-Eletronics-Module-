<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\StoreInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PaymentCollection;
use App\Models\Store;
use App\Models\StoreNote;
use App\Models\Invoice;
use App\Models\Ledger;

class StoreController extends Controller
{
    public function __construct(StoreInterface $storeRepository)
    {
        $this->storeRepository = $storeRepository;
    }
    /**
     * This method is for show store list
     *
     */

    public function list(Request $request): JsonResponse
    {
        $search = !empty($request->search)?$request->search:'';



        $data = Store::select('id','store_name','bussiness_name','email','contact','whatsapp','status','is_approved','shipping_address AS address');

        if(!empty($search)){
            $data = $data->where('store_name', 'LIKE', '%'.$search.'%')->orWhere('bussiness_name', 'LIKE', '%'.$search.'%')->orWhere('contact', 'LIKE', '%'.$search.'%');
        }
        
        $data = $data->where('status', 1)->orderBy('store_name','asc')->get();

        return response()->json(['error'=>false,'message'=>"Store List", 'data'=> array('store'=>$data) ],200);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "created_by" => "required|numeric|exists:users,id",   
            "whatsapp"=>"required|unique:stores|max:10"            
        ]);
        
        $params = $request->except('_token');
        
        if (!$validator->fails()) {
            // dd($params);
            $data = $this->storeRepository->create($params);
            $attendance_id = !empty($params['attendance_id'])?$params['attendance_id']:0;
            $latitude = !empty($params['latitude'])?$params['latitude']:'';
            $longitude = !empty($params['longitude'])?$params['longitude']:'';
            $store_id = $data->id;

            if(!empty($attendance_id) && !empty($latitude) && !empty($longitude)){
                updatelocationattendance($attendance_id,$latitude,$longitude,$store_id);
            }
            
            return response()->json(
                [
                    'status' => 201, 
                    'error' => false, 
                    'message' => 'Store Created', 
                    'data' => $data
                ], 
                Response::HTTP_CREATED
            );

        } else {
            return response()->json(
                [
                    'status' => 400, 
                    'error' => true, 
                    'message' => 'Validation', 
                    'data' => $validator->errors()->first()
                ]
            );
        }

    }

    public function noorder(Request $request)
    {
        // $newDetails = $request->only([
        //     'user_id', 'start_location', 'end_location', 'start_lat', 'end_lat', 'start_lng', 'end_lng', 'start_date', 'end_date', 'start_time', 'end_time'
        // ]);

        $data = $request->only([
            'user_id', 'store_id', 'comment', 'lat','lng','location','visit_image'
        ]);
        //dd($data);
        $stores = $this->storeRepository->noorderreasonupdate($data);
         //dd($stores);
        return response()->json(['error'=>false, 'resp'=>'No order Reason data created successfully','data'=>$stores]);
    }

    
    public function taskStoreList($id): JsonResponse{
        
        $start_date = date("Y-m-d", strtotime("last sunday"));
        $end_date = date("Y-m-d", strtotime("next saturday"));

        $tasks = DB::table('tasks')->where('user_id',$id)->where('start_date',$start_date)->where('end_date',$end_date)->first();

        $stores = array();
        if(!empty($tasks)){
            $stores = DB::table('task_details AS td')->select('stores.id','stores.store_name','stores.contact','stores.email','td.no_of_visit','td.comment')->leftJoin('stores', 'stores.id','td.store_id')->where('td.task_id',$tasks->id)->get()->toarray();
        }

        return response()->json(['error'=>false, 'resp'=>'store list successfully','data'=>$stores]);
    }

    public function invoices(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'id' => ['required', 'integer', 'min:1', 'exists:stores,id'],
            'take' => ['integer'],
            'page' => ['integer']
        ]);        

        if(!$validator->fails()){
            $params = $request->except('_token');
            $id = $params['id'];
            
            
            // $invoices = DB::table('invoice AS i')
            //             ->select('i.id','i.invoice_no','i.net_price','i.payment_status','i.required_payment_amount')
            //             ->where('i.store_id', $id)
            //             ->orderBy('i.id','desc')
            //             // ->skip($skip)->take($take)
            //             ->get()->toarray();

            $invoices = Invoice::select('id','order_id','packingslip_id','invoice_no','created_at','net_price','is_gst')->with('order:id,order_no')->with('packingslip:id,slipno')->where('store_id',$id)->orderBy('id','desc')->get();

            # last three payments

            $payment_collections = PaymentCollection::select('id','user_id','store_id','collection_amount','payment_type','bank_name','cheque_number','cheque_date','is_ledger_added')->where('store_id',$id)->orderBy('id','desc')->take(3)->get()->toarray();

            # unpaid amount

            $store_unpaid_amount = Invoice::where('store_id',$id)->sum('required_payment_amount');
            
            return response()->json(['error'=>false,'message'=>"Store invoices", 'data'=> array('store_unpaid_amount'=>$store_unpaid_amount, 'payments'=>$payment_collections, 'invoices'=>$invoices) ],200);
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
               
                
    }

    public function createnote(Request $request)
    {
        # save notes for store...
        $validator = Validator::make($request->all(),[
            'user_id' => ['required','exists:users,id'],
            'store_id' => ['required','exists:stores,id'],
            'details' => ['required'],
            'latitude' => ['string','nullable'],
            'longitude' => ['string','nullable'],
            'attendance_id' => ['nullable','exists:user_attendances,id'],
        ]);

        if($validator->fails()){
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        } else {
            $params = $request->except('_token');
            // dd($params);
            if(!in_array($params['user_id'],[1,2])){
                if(empty($request->latitude)){
                    return response()->json(['status'=>400,'message'=>"Please add latitude"], 400);
                }
                if(empty($request->longitude)){
                    return response()->json(['status'=>400,'message'=>"Please add longitude"], 400);
                }
                if(empty($request->attendance_id)){
                    return response()->json(['status'=>400,'message'=>"Please add attendance id"], 400);
                }

                $checkattendance = DB::table('user_attendances')->find($params['attendance_id']);

                if($checkattendance->user_id != $params['user_id']){
                    return response()->json(
                        [
                            'error' => true,
                            'message' => "This is not you attendacne id ",
                            'data' => (object) []
                        ],
                        200
                    );
                }
                if($checkattendance->start_date != date('Y-m-d')){
                    return response()->json(
                        [
                            'error' => true,
                            'message' => "This is not today's attendance id",
                            'data' => (object) []
                        ],
                        200
                    );
                }
            }
            
            
            StoreNote::insert([
                'store_id' => $params['store_id'],
                'user_id' => $params['user_id'],
                'details' => $params['details'],
                'created_at'=>date('Y-m-d H:i:s')
            ]);

            if(!in_array($params['user_id'], [1,2])){
                $attendance_id = $params['attendance_id'];
                $latitude = $params['latitude'];
                $longitude = $params['longitude'];
                updatelocationattendance($attendance_id,$latitude,$longitude,$params['store_id']);
            }

            return response()->json(['error'=>false,'message'=>"Notes created successfully", 'data'=> array() ],200);
        }

        

    }

    public function listnote(Request $request)
    {
        # list note ...
        $validator = Validator::make($request->all(),[
            'user_id' => ['required','exists:users,id'],
            'store_id' => ['required','exists:stores,id']
        ]);

        if($validator->fails()){
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        } else {
            $params = $request->except('_token');

            $data = StoreNote::where('user_id',$params['user_id'])->where('store_id', $params['store_id'])->get();

            return response()->json(['error'=>false,'message'=>"My store notes", 'data'=> array('notes' => $data) ],200);
        }        
    }

    public function downloadinvoice($invoice_no)
    {
        if(!empty($invoice_no)){
            $invoice = Invoice::select('*')->with('order','store','user','products')->where('invoice_no',$invoice_no)->first();
            $invpdfname = $invoice_no."";
            if(!empty($invoice->is_gst)){
                $pdf = Pdf::loadView('admin.packingslip.invoice', compact('invoice'));
                return $pdf->download($invpdfname.'.pdf');
            } else {
                $pdf = Pdf::loadView('admin.packingslip.cashslip', compact('invoice'));
                return $pdf->download($invpdfname.'.pdf');
            }            
            
        } else {

        }
    }

    public function unpaid_payments(Request $request)
    {
        # store unpaid invoice...

        $data = Ledger::select('store_id')->with('store')->where('user_type', 'store')->groupBy('store_id');
               
        $data = $data->get()->toArray();
        $myArr = array();
        foreach($data as $key => $item){
            $total_payment = PaymentCollection::where('store_id', $item['store_id'])->sum('collection_amount');
            // echo 'total_payment:- '.($total_payment).'<br/>';
            $bills = Ledger::where('store_id', $item['store_id'])->select('id','purpose','transaction_id','is_credit','is_debit','transaction_amount','entry_date')->where('is_debit', 1)->orderBy('entry_date')->get()->toArray();

            $total = 0;
            $invoice_date = '';

            $getStoreLedgerAmount = getStoreLedgerAmount($item['store_id']);
            $outstanding = $getStoreLedgerAmount['outstanding'];
            $due_days = 0;
            
            
            foreach($bills as $bill){
                $total += $bill['transaction_amount'];                
                if($total > $total_payment){
                    $invoice_date = $bill['entry_date'];
                    break;
                }
            }

            if(!empty($invoice_date)){
                $due_days = date_diff(
                    date_create($invoice_date),  
                    date_create(date('Y-m-d'))
                )->format('%a');

                $due_days = (int) $due_days;
            }
            
            $myArr[] = array('store_id'=>$item['store_id'],'store_name'=>$item['store']['bussiness_name'],'amount'=>$outstanding,'due_days' => $due_days,'invoice_date'=>$invoice_date);            
            // dd($myArr);
        }        

        $finalArr = array();
        foreach($myArr as $arr){
            if($arr['amount'] != 0 || $arr['amount'] < 0){                
                if(!empty($days_above) && !empty($amount_above)){                    
                    if($arr['due_days'] >= $days_above && replaceMinusSign($arr['amount']) >= $amount_above){
                        // die('Hi');
                        $finalArr[] = array('store_id'=>$arr['store_id'],'store_name'=>$arr['store_name'],'amount'=>$arr['amount'],'due_days'=>$arr['due_days'],'invoice_date'=>$arr['invoice_date']);
                        // dd($finalArr);
                    }
                    
                } else {
                    if(!empty($days_above) && empty($amount_above)){
                        if($arr['due_days'] >= $days_above){
                            $finalArr[] = array('store_id'=>$arr['store_id'],'store_name'=>$arr['store_name'],'amount'=>$arr['amount'],'due_days'=>$arr['due_days'],'invoice_date'=>$arr['invoice_date']);
                        }
                    } else if (empty($days_above) && !empty($amount_above)){
                        if(replaceMinusSign($arr['amount']) >= $amount_above){
                            $finalArr[] = array('store_id'=>$arr['store_id'],'store_name'=>$arr['store_name'],'amount'=>$arr['amount'],'due_days'=>$arr['due_days'],'invoice_date'=>$arr['invoice_date']);
                        }
                    } else {
                        
                            $finalArr[] = array('store_id'=>$arr['store_id'],'store_name'=>$arr['store_name'],'amount'=>$arr['amount'],'due_days'=>$arr['due_days'],'invoice_date'=>$arr['invoice_date']);
                        
                    }
                    
                }
                
            }
        }

        usort($finalArr, function($a, $b) {
            return $a['due_days'] <= $b['due_days'];
        });

        return response()->json(['error'=>false,'message'=>"Unpaid Store Payment", 'data'=> array('count_list' => count($finalArr),'list' => $finalArr) ],200);
        
        
    }

}
