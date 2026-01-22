<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Interfaces\PaymentCollectionInterface;
use App\Models\PaymentCollection;
use Illuminate\Support\Facades\DB;

class PaymentCollectionController extends Controller
{
    public function __construct(PaymentCollectionInterface $paymentCollectionRepository)
    {
        $this->paymentCollectionRepository = $paymentCollectionRepository;
    }

    public function listByStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'store_id' => ['required', 'exists:stores,id'],
            'type' => ['required', 'in:today,lastweek,lastmonth,lastyear,custom'],
            'from_date' => ['nullable','required_if:type,custom' , 'date', 'date_format:Y-m-d'],
            'to_date' => ['nullable','required_if:type,custom' , 'date', 'date_format:Y-m-d'],
            'take' => ['integer'],
            'page' => ['nullable']
        ]);

        $params = $request->except('_token');
        if(!$validator->fails()){
            $data = $this->paymentCollectionRepository->listByStore($params);
            
            return response()->json(['error'=>false, 'resp'=>'Last three payment collection of store','data'=>$data]);
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
       
    }

    
    public function store(Request $request): JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
            'store_id' => ['required', 'integer', 'min:1'],
            'collection_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'cheque_date' => ['required', 'date', 'date_format:Y-m-d' , 'before_or_equal:'.date('Y-m-d')],
            'payment_type' => ['required','field' => 'in:cash,cheque,neft'],
            'latitude' => ['string','nullable'],
            'longitude' => ['string','nullable'],
            'attendance_id' => ['nullable','exists:user_attendances,id'],
        ],[
            'cheque_date.required' => "Please mention payment date",
            'cheque_date.before_or_equal' => "Upcoming date is not allowed"
        ]);

        $params = $request->except('_token');
        if (!$validator->fails()) {

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
            

            /*$check_store_unpaid_invoices = DB::table('invoice')->where('store_id', $params['store_id'])->where('is_paid', 0)->get()->toarray();

            if(empty($check_store_unpaid_invoices)){
                return response()->json(
                    [
                        'status' => 200, 
                        'error' => true, 
                        'message' => 'No unpaid invoice found of the store', 
                        'data' => (object) []
                    ],200
                );
            }*/

            $check_outstanding_amount = DB::table('invoice')->where('store_id',$request->store_id)->where('is_paid',0)->sum('required_payment_amount');

            $check_not_receipt_payment_amount = DB::table('payment_collections')->where('store_id',$request->store_id)->where('is_ledger_added',0)->first();

            if($request->collection_amount > $check_outstanding_amount){
                // die('Please decrease your amount value. Unpaid outstanding amount is '.$check_outstanding_amount);
                return response()->json(
                    [
                        'status' => 200, 
                        'error' => true, 
                        'message' => 'Please decrease your amount value. Unpaid outstanding amount is '.$check_outstanding_amount, 
                        'data' => (object) []
                    ]
                );
            }else{
                $check_not_receipt_payment_amount = DB::table('payment_collections')->where('store_id',$request->store_id)->where('is_ledger_added',0)->first();

                if(!empty($check_not_receipt_payment_amount)){
                    return response()->json(
                        [
                            'status' => 200, 
                            'error' => true, 
                            'message' => 'You have already collected amount Rs'.$check_not_receipt_payment_amount->collection_amount.' for this store. Please let add this payment from accountant ', 
                            'data' => (object) []
                        ]
                    ); 
                }else{
                    $data = $this->paymentCollectionRepository->create($params);
                    if(!in_array($params['user_id'], [1,2])){
                        $attendance_id = $params['attendance_id'];
                        $latitude = $params['latitude'];
                        $longitude = $params['longitude'];
                        updatelocationattendance($attendance_id,$latitude,$longitude,$params['store_id']);
                    }
                    return response()->json(
                        [
                            'status' => 201, 
                            'error' => false, 
                            'message' => 'Payment collection added', 
                            'data' => $data
                        ], 
                        Response::HTTP_CREATED
                    );
                }
            }

            // dd($check_outstanding_amount);


            

        } else {
            return response()->json(
                [
                    'status' => 400, 
                    'error' => true, 
                    'message' => 'Validation', 
                    'data' => $validator->errors()->first()
                ],400
            );
        }

    }

}
