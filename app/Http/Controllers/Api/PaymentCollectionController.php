<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Interfaces\PaymentCollectionInterface;
use App\Models\PaymentCollection;
use App\Models\Payment;
use App\Models\Ledger;
use App\Models\Journal;
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

    public function saveExpenses(Request $request){
        $store_id    = $request->store_id ?? '';
        $staff_id    = $request->staff_id ?? '';
        $admin_id    = $request->admin_id ?? '';
        $supplier_id = $request->supplier_id ?? '';
        $user_type   = $request->user_type ?? '';
        $expense_id  = $request->expense_id ?? '';
        $expense_proof  = $request->expense_proof ?? '';

        /* ================= VALIDATION ================= */

         if ($user_type != 'miscellaneous') {
            $validator = Validator::make($request->all(), [
                'payment_date' => 'required',
                'payment_mode' => 'required',
                'amount'       => 'required|numeric',
                'user_type'    => 'required',
                'user_id'      => 'required',
                'user_name'    => 'required',
                'expense_id'   => 'required',
                'expense_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'payment_date' => 'required',
                'payment_mode' => 'required',
                'amount'       => 'required|numeric',
                'user_type'    => 'required',
                'expense_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
            ]);
        }


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

         DB::beginTransaction();

         try{

            $upload_path = public_path('uploads/expense-proof');
            $expense_proof_name = null;

            if ($request->hasFile('expense_proof')) {
                $file = $request->file('expense_proof');

                // get original extension
                $extension = $file->getClientOriginalExtension();

                // create unique file name
                $expense_proof_name = 'expense_' . time() . '.' . $extension;

                // move file
                $file->move($upload_path, $expense_proof_name);
            }

             /* ================= PAYMENT INSERT ================= */
              $paymentData = [
                'payment_for'  => 'debit',
                'voucher_no'   => $request->voucher_no,
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
                'payment_in'   => ($request->payment_mode != 'cash') ? 'bank' : 'cash',
                'bank_cash'    => ($request->payment_mode == 'cash') ? 'cash' : 'bank',
                'amount'       => $request->amount,
                'bank_name'    => $request->bank_name,
                'chq_utr_no'   => $request->chq_utr_no,
                'narration'    => $request->narration,
                'expense_proof' =>  $expense_proof_name,
                'created_by'   => auth()->id()
            ];

             if ($user_type == 'staff') {
               $paymentData['staff_id'] = $staff_id;
            } elseif ($user_type == 'store') {
                $paymentData['store_id'] = $store_id;
            } elseif ($user_type == 'partner') {
                $paymentData['admin_id'] = $admin_id;
            } elseif ($user_type == 'supplier') {
                $paymentData['supplier_id'] = $supplier_id;
            }

            if (!empty($expense_id)) {
                $paymentData['expense_id'] = $expense_id;
            }

            $payment_id = Payment::insertGetId($paymentData);

             $is_credit = 0;
             $is_debit  = 1;

              /* ================= PURPOSE ================= */

            $expense_name = $request->expense_name ?? '';
            $purpose_description = "expense for ".$user_type.". ".$expense_name;

             /* ================= STAFF CONTRA ENTRY ================= */

            if ($user_type == 'staff' && !empty($staff_id)) {

                $checkExpense = DB::table('expense')->find($expense_id);

                if (!empty($checkExpense) && !empty($checkExpense->for_credit)) {

                    Ledger::insert([
                        'user_type'           => $user_type,
                        'staff_id'            => $staff_id,
                        'transaction_id'      => 'STAFFEXPENSE'.time(),
                        'transaction_amount'  => $request->amount,
                        'payment_id'          => $payment_id,
                        'bank_cash'           => ($request->payment_mode == 'cash') ? 'cash' : 'bank',
                        'is_credit'           => 1,
                        'entry_date'          => $request->payment_date,
                        'purpose'             => 'staff_expense',
                        'purpose_description' => "Contra Entry For ".$expense_name
                    ]);
                }
            }

             /* ================= MAIN LEDGER ENTRY ================= */

            if ($user_type != 'miscellaneous') {

                $ledgerData = [
                    'user_type'           => $user_type,
                    'transaction_id'      => $request->voucher_no,
                    'transaction_amount'  => $request->amount,
                    'payment_id'          => $payment_id,
                    'bank_cash'           => ($request->payment_mode == 'cash') ? 'cash' : 'bank',
                    'is_credit'           => $is_credit,
                    'is_debit'            => $is_debit,
                    'entry_date'          => $request->payment_date,
                    'purpose'             => 'expense',
                    'purpose_description' => $purpose_description
                ];

                if ($user_type == 'staff') {
                    $ledgerData['staff_id'] = $staff_id;
                } elseif ($user_type == 'store') {
                    $ledgerData['store_id'] = $store_id;
                } elseif ($user_type == 'partner') {
                    $ledgerData['admin_id'] = $admin_id;
                } elseif ($user_type == 'supplier') {
                    $ledgerData['supplier_id'] = $supplier_id;
                }

                Ledger::insert($ledgerData);
            }

            /* ================= JOURNAL ENTRY ================= */
            
            Journal::insert([
                'transaction_amount'  => $request->amount,
                'is_credit'           => $is_credit,
                'is_debit'            => $is_debit,
                'entry_date'          => $request->payment_date,
                'payment_id'          => $payment_id,
                'bank_cash'           => ($request->payment_mode == 'cash') ? 'cash' : 'bank',
                'purpose'             => 'expense',
                'purpose_description' => $purpose_description,
                'purpose_id'          => $request->voucher_no
            ]);


             /* ================= WITHDRAWAL UPDATE ================= */

            if (!empty($request->withdrawls_id)) {
                DB::table('withdrawls')
                    ->where('id', $request->withdrawls_id)
                    ->update(['is_disbursed' => 1]);
            }

            DB::commit();

            return response()->json([
                'status'     => true,
                'message'    => !empty($request->withdrawls_id)
                    ? 'Withdrawal disbursed successfully'
                    : 'Expense added successfully',
                'payment_id' => $payment_id
            ]);




         }catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }

    }

}
