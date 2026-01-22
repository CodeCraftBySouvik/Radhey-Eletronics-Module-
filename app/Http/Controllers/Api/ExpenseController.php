<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Models\Payment;
use App\Models\Ledger;
use App\Models\Journal;

class ExpenseController extends Controller
{
    //

    /* List Expense Type */

    public function types(Request $request)
    {
        # partner debit expense types...        
        
        $payment_for = !empty($request->payment_for)?$request->payment_for:'credit';
        $expense_for = !empty($request->expense_for)?$request->expense_for:'partner';
        $data = DB::table('expense')->select('id','title','for_partner','for_staff','for_store','for_credit','for_debit');   
        if(!empty($payment_for)){
            if($payment_for == 'credit'){
                $data = $data->where('for_credit', 1);
            } else {
                $data = $data->where('for_debit', 1);
            }
        } 
        if(!empty($expense_for)){
            if($expense_for == 'partner'){
                $data = $data->where('for_partner', 1); 
            } else if ($expense_for == 'staff'){
                $data = $data->where('for_staff', 1); 
            } else if ($expense_for == 'store'){
                $data = $data->where('for_store', 1); 
            } else if ($expense_for == 'supplier'){
                $data = $data->where('slug', 'supplier-payment');
            }
        }    
              
        
        
        $data = $data->where('status', 1)->orderBy('title','asc')->get();

        return response()->json(['error' => false, 'message' => 'Expense List', 'data' => array('types'=>$data) ], 200);
    }

    /* List Expense */

    public function list($admin_id=0)
    {
        # expense list...        

        if(!empty($admin_id)){
            if(in_array($admin_id,[1,2])){
                $data = Payment::select('*')
                // ->select('id','admin_id','expense_id','payment_for','bank_cash','voucher_no','payment_date','amount','chq_utr_no','bank_name','narration','created_from')
                ->with('expense:id,title')
                ->where('admin_id',$admin_id)
                ->where('payment_for','credit')
                ->whereNotNull('expense_id')
                ->orderBy('payment_date','desc')
                ->orderBy('id','desc')->get();
                
                $count_data = Payment::where('admin_id',$admin_id)
                ->where('payment_for','credit')
                ->whereNotNull('expense_id')->count();

                // echo '<pre>'; print_r($data);
                return response()->json(['error' => false, 'message' => 'Expense List', 'data' => array('count_data'=>$count_data,'expenses'=>$data) ], 200);
            } else {
                return response()->json(['error' => true, 'message' => 'You have no authorization for accessing expense list', 'data' => array() ], 200);
            }
            
        } else {
            return response()->json(['error' => true, 'message' => 'Please mention admin_id', 'data' => array() ], 200);
        }

        
    }

    /* Add Expense */

    public function add(Request $request)
    {
        # expense add...

        $voucher_no = "PTREXP".time();
        $validator = Validator::make($request->all(), [
            'admin_id' => ['required','exists:users,id'],
            'payment_date' => ['required', 'date', 'date_format:Y-m-d' , 'before_or_equal:'.date('Y-m-d')],
            'amount' => ['required'],   
            'payment_mode' => ['required','in:cheque,neft,cash'],
            'bank_name' => ['required_unless:payment_mode,cash'],
            'chq_utr_no' => ['required_unless:payment_mode,cash'],
            'expense_id' => ['exists:expense,id','required']
        ]);
        // $payment_date = 
        $params = $request->except('_token');

        $expense_at = 'partner';
        $admin_id = $expense_id = 0;
        
        $admin_id = $params['admin_id'];
        $expense_id = $params['expense_id'];
        

        if(!$validator->fails()){
            
            if(!in_array($admin_id,[1,2])){
                return response()->json(['error' => false, 'message' => "You have no authorization to add expense ", 'data' => array() ], 200);
            }


            $payment_id = Payment::insertGetId([               
                'admin_id' => $admin_id,
                'payment_for' => 'credit',
                'expense_id' => $expense_id,
                'voucher_no' => $voucher_no,
                'payment_date' => $params['payment_date'],
                'payment_mode' => $params['payment_mode'],
                'payment_in' => ($params['payment_mode'] != 'cash') ? 'bank' : 'cash' ,
                'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
                'amount' => $params['amount'],
                'bank_name' => !empty($params['bank_name'])?$params['bank_name']:'',
                'chq_utr_no' => !empty($params['chq_utr_no'])?$params['chq_utr_no']:'',
                'narration' => !empty($params['narration'])?$params['narration']:'',
                'created_from' => 'app',
                'created_by' => $admin_id,
                'created_at'=>date('Y-m-d H:i:s')
            ]);

            $is_credit = 1; 
            $is_debit = 0;


            /* Add expense in purpose */
            $expense_name = "";
            if(!empty($expense_id)){
                $expense_title = DB::table('expense')->select('title')->find($expense_id);
                $expense_name = $expense_title->title;
            }
            $purpose_description = "expense for ".$expense_at.". ".$expense_name;
            /* ====================== */

            
            Ledger::insert([
                'user_type' => $expense_at,
                'admin_id' => $admin_id,
                'transaction_id' => $voucher_no,
                'transaction_amount' => $params['amount'],
                'payment_id' => $payment_id,
                'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
                'is_credit' => $is_credit,
                'is_debit' => $is_debit,
                'entry_date' => $params['payment_date'],
                'purpose' => 'expense',
                'purpose_description' => $purpose_description,
                'created_at'=>date('Y-m-d H:i:s')
            ]);
        
            $successMsg = "Expense added successfully for ".$expense_name."";
            $resultPayment = DB::table('payment')->find($payment_id);

            return response()->json(['error' => false, 'message' => $successMsg, 'data' => array('expense'=>$resultPayment) ], 200);

        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }

    }

    public function add_depot_expense(Request $request)
    {
        # add depot expense...
        $validator = Validator::make($request->all(), [
            'admin_id' => ['required', 'exists:users,id'],
            'user_type' => ['required', 'field' => 'in:staff,store,supplier,miscellaneous'],
            'user_id' => ['required_if:user_type,staff,store,supplier'],
            'payment_date' => ['required','date','date_format:Y-m-d','before_or_equal:'.date('Y-m-d')],
            'amount' => ['required'],
            'payment_mode' => ['required', 'field' => 'in:cheque,neft,cash'],
            'chq_utr_no' => ['required_if:payment_mode,cheque,neft'],
            'bank_name' => ['required_if:payment_mode,cheque,neft'],
            'expense_id' => ['required_if:user_type,staff,store,supplier', 'exists:expense,id']
        ]);

        if(!$validator->fails()){
            $params = $request->except('_token');
            $admin_id = $params['admin_id'];
            $params['voucher_no'] = "EXPENSE".time();           
            $expense_name = '';
            if(!empty($params['expense_id'])){
                $expense_name = getSingleAttributeTable('expense',$params['expense_id'],'title');
            }
            $paymentData = array(            
                'payment_for' => 'debit',
                'voucher_no' => $params['voucher_no'],
                'payment_date' => $params['payment_date'],
                'payment_mode' => $params['payment_mode'],
                'payment_in' => ($params['payment_mode'] != 'cash') ? 'bank' : 'cash' ,
                'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
                'amount' => $params['amount'],
                'bank_name' => !empty($params['bank_name'])?$params['bank_name']:NULL,
                'chq_utr_no' => !empty($params['chq_utr_no'])?$params['chq_utr_no']:NULL,
                'narration' => !empty($params['narration'])?$params['narration']:NULL,
                'created_by' => $admin_id,
                'created_from' => 'app'
            ); 

            if($params['user_type'] == 'miscellaneous'){

            } else {
                if($params['user_type'] == 'staff'){
                    $paymentStaff = array('staff_id' => $params['user_id']);
                    $paymentData = array_merge($paymentData,$paymentStaff);
                } else if ($params['user_type'] == 'store'){
                    $paymentStore = array('store_id' => $params['user_id']);
                    $paymentData = array_merge($paymentData,$paymentStore);
                } else if ($params['user_type'] == 'supplier'){
                    $paymentSupplier = array('supplier_id' => $params['user_id']);
                    $paymentData = array_merge($paymentData,$paymentSupplier);
                }
            }  
            
            if(!empty($params['expense_id'])){
                $paymentExpense = array('expense_id' => $params['expense_id']);
                $paymentData = array_merge($paymentData,$paymentExpense);
            }

            // dd($paymentData);
            $payment_id = Payment::insertGetId($paymentData);
            $is_credit = 0; 
            $is_debit = 1;
            /* Add expense in purpose */            
            $purpose_description = "expense for ".$params['user_type'].". ".$expense_name;
            /* ====================== */
            if($params['user_type'] != 'miscellaneous'){
                $ledgerData = array(
                    'user_type' => $params['user_type'],
                    'transaction_id' => $params['voucher_no'],
                    'transaction_amount' => $params['amount'],
                    'payment_id' => $payment_id,
                    'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
                    'is_credit' => $is_credit,
                    'is_debit' => $is_debit,
                    'entry_date' => $params['payment_date'],
                    'purpose' => 'expense',
                    'purpose_description' => $purpose_description,
                    'created_at'=>date('Y-m-d H:i:s')
                );
                if($params['user_type'] == 'staff'){
                    $ledgerStaff = array('staff_id' => $params['user_id']);
                    $ledgerData = array_merge($ledgerData,$ledgerStaff);
                } else if ($params['user_type'] == 'store'){
                    $ledgerStore = array('store_id' => $params['user_id']);
                    $ledgerData = array_merge($ledgerData,$ledgerStore);
                } else if ($params['user_type'] == 'partner'){
                    $ledgerAdmin = array('admin_id' => $params['user_id']);
                    $ledgerData = array_merge($ledgerData,$ledgerAdmin);
                } else if ($params['user_type'] == 'supplier'){
                    $ledgerSupplier = array('supplier_id' => $params['user_id']);
                    $ledgerData = array_merge($ledgerData,$ledgerSupplier);
                }
                // dd($ledgerData);            
                Ledger::insert($ledgerData);
            }        
            /* Entry in journal */
            Journal::insert([
                'transaction_amount' => $params['amount'],
                'is_credit' => $is_credit,
                'is_debit' => $is_debit,
                'entry_date' => $params['payment_date'],
                'payment_id' => $payment_id,
                'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 
                'purpose' => 'expense',
                'purpose_description' =>  $purpose_description ,
                'purpose_id' => $params['voucher_no'],
                'created_at'=>date('Y-m-d H:i:s')
            ]);

            return response()->json(['error' => false, 'message' => "Depot expense added successfully", 'data' => array() ], 200);
            
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
    }
}
