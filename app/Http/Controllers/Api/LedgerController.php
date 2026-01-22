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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Payment;
use App\Models\Ledger;
use App\Models\Journal;

class LedgerController extends Controller
{
    //

    public function search_user(Request $request)
    {
        # code...

        $validator = Validator::make($request->all(),[
            'admin_id' => ['required', 'exists:users,id'],
            'user_type' => ['required', 'in:store,staff,supplier,partner'],
            'search' => ['nullable']
        ]);

        if(!$validator->fails()){
            $params = $request->except('_token');
            $admin_id = $params['admin_id'];
            $search = $params['search'];
            $user_type = $params['user_type'];

            $data = array();

            if ($user_type == 'staff'){

                $data = DB::table('users')->select('id','name');
                if(!empty($search)){
                    $data = $data->where('name','LIKE','%'.$search.'%');
                }                
                $data = $data->where('type',2)->orderBy('name')->get();

            } else if ($user_type == 'store'){

                $data = DB::table('stores')->select('id','bussiness_name AS name');                
                if(!empty($search)){
                    $data = $data->where('bussiness_name','LIKE','%'.$search.'%');
                }                
                $data = $data->orderBy('bussiness_name')->get();

            } else if ($user_type == 'supplier'){

                $data = DB::table('suppliers')->select('id','name');
                if(!empty($search)){
                    $data = $data->where('name','LIKE','%'.$search.'%');
                }
                $data = $data->where('status',1)->get();

            } else if ($user_type == 'partner'){

                $data = DB::table('users')->select('id','name');
                if(!empty($search)){
                    $data = $data->where('name','LIKE','%'.$search.'%');
                }
                $data = $data->where('type',1)->get();

            }

            return response()->json(['error' => false, 'message' => "User Search Result", 'data' => array('user_type' => $user_type, 'list' => $data) ], 200);
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
        
    }

    public function list(Request $request)
    {
        # user ledger data...
        $validator = Validator::make($request->all(),[
            'from_date' => ['required', 'date','date_format:Y-m-d','before_or_equal:'.date('Y-m-d')],
            'to_date' => ['required', 'date','date_format:Y-m-d','before_or_equal:'.date('Y-m-d')],
            'user_type' => ['required', 'in:store,staff,supplier,partner'],
            'store_id' => ['nullable','required_if:user_type,store','exists:stores,id'],
            'staff_id' => ['nullable','required_if:user_type,staff','exists:users,id'],
            'admin_id' => ['nullable','required_if:user_type,partner','in:1,2'],
            'supplier_id' => ['nullable','required_if:user_type,supplier','exists:suppliers,id'],
            'bank_cash' => ['nullable','in:bank,cash']
        ]);

        if(!$validator->fails()){
            $params = $request->except('_token');
            $user_type = $params['user_type'];
            $bank_cash = !empty($params['bank_cash'])?$params['bank_cash']:'';
            $store_id = !empty($params['store_id'])?$params['store_id']:NULL;
            $staff_id = !empty($params['staff_id'])?$params['staff_id']:NULL;
            $admin_id = !empty($params['admin_id'])?$params['admin_id']:NULL;
            $supplier_id = !empty($params['supplier_id'])?$params['supplier_id']:NULL;
            
            $from_date = !empty($params['from_date'])?$params['from_date']:date('Y-m-01', strtotime(date('Y-m-d')));
            $to_date = !empty($params['to_date'])?$params['to_date']:date('Y-m-d');

            // dd($params);
            $data = $outstanding = array();
            $day_opening_amount = $is_opening_bal =  0;
            $non_tr_day_opening_amount = 0;
            $is_opening_bal_showable = 1;
            $opening_bal_date = "";

            $data = Ledger::select('id','user_type','transaction_id','transaction_amount','is_credit','is_debit','bank_cash','entry_date','purpose','purpose_description');
            
            $opening_bal = Ledger::select('*');

            if($user_type == 'store' && !empty($store_id)){
                $data = $data->where('user_type', 'store')->where('store_id',$store_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('store_id',$store_id);
            }else if($user_type == 'staff'  && !empty($staff_id)){
                $data = $data->where('user_type', 'staff')->where('staff_id',$staff_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('staff_id',$staff_id);
            }else if($user_type == 'partner' && !empty($admin_id)){
                $data = $data->where('user_type', 'partner')->where('admin_id',$admin_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('admin_id',$admin_id);
            }else if($user_type == 'supplier' && !empty($supplier_id)){
                $data = $data->where('user_type','supplier')->where('supplier_id',$supplier_id);
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('supplier_id',$supplier_id);
            }
            /* ++++++++CHECK OPENING BALANCE STORE+++++++++++ */
            if($user_type == 'store'){
                $check_ob_exist_store = Ledger::where('purpose','opening_balance')->where('user_type', 'store')->where('store_id',$store_id)->orderBy('id','asc')->first();

                if(!empty($check_ob_exist_store)){
                    // dd('Hi');
                    $from_date = ($params['from_date'] < $check_ob_exist_store->entry_date) ? $check_ob_exist_store->entry_date : $params['from_date'];
                    // dd('Hi:- '.$from_date);
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
            }            
            /* ++++++++CHECK OPENING BALANCE PARTNER+++++++++++ */
            if($user_type == 'partner'){
                $check_ob_exist_partner = Ledger::where('purpose','opening_balance')->where('user_type', 'partner')->where('admin_id',$admin_id)->orderBy('id','asc')->first();

                if(!empty($check_ob_exist_partner)){
                    $from_date = ($params['from_date'] < $check_ob_exist_partner->entry_date) ? $check_ob_exist_partner->entry_date : $params['from_date'];
                    $is_opening_bal = 1;
                    $opening_bal_date = $check_ob_exist_partner->entry_date;

                    if($from_date == $check_ob_exist_partner->entry_date){                    
                        $is_opening_bal_showable = 0;                    
                    } else {
                        $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_partner->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                    }                
                } else {
                    $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                } 
            }
            if(!empty($from_date) && !empty($to_date)){
                $data = $data->whereRaw("entry_date BETWEEN '".$from_date."' AND '".$to_date."' ");
            }            
            $opening_bal = $opening_bal->orderBy('entry_date','asc');  
            $opening_bal = $opening_bal->orderBy('updated_at','asc');  
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
                $data = $data->where('bank_cash', $bank_cash);
            }
            // dd($day_opening_amount);
            $data = $data->orderBy('entry_date','asc');  
            $data = $data->orderBy('updated_at','asc');  
            $data = $data->get()->toarray();  
            
            
            if(empty($data)){
                // dd('Empty');
                $non_tr_opening_bal = Ledger::select('*');
                if($user_type == 'store' && !empty($store_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'store')->where('store_id',$store_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'staff'  && !empty($staff_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'staff')->where('staff_id',$staff_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'partner' && !empty($admin_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'partner')->where('admin_id',$admin_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'supplier' && !empty($supplier_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type','supplier')->where('supplier_id',$supplier_id);
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }

                $non_tr_opening_bal = $non_tr_opening_bal->whereRaw("entry_date BETWEEN '".$started_date."' AND '".$to_date."' ")->get();

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
            
            return response()->json([
                'error' => false, 
                'message' => ucwords($user_type)." Ledger Data", 
                'data' => array(
                    'user_type' => $user_type,
                    'day_opening_amount'=>$day_opening_amount,
                    'is_opening_bal'=>$is_opening_bal,
                    'is_opening_bal_showable'=>$is_opening_bal_showable,
                    'opening_bal_date'=>$opening_bal_date,
                    'non_tr_day_opening_amount'=>$non_tr_day_opening_amount,
                    'from_date' => $from_date,
                    'to_date' => $to_date,
                    'count_list' => count($data),
                    'list' => $data
                ) 
            ], 200);

        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
    }

    public function csv(Request $request)
    {
        # csv download...
        $validator = Validator::make($request->all(),[
            'from_date' => ['required', 'date','date_format:Y-m-d','before_or_equal:'.date('Y-m-d')],
            'to_date' => ['required', 'date','date_format:Y-m-d','before_or_equal:'.date('Y-m-d')],
            'user_type' => ['required', 'in:store,staff,supplier,partner'],
            'store_id' => ['nullable','required_if:user_type,store','exists:stores,id'],
            'staff_id' => ['nullable','required_if:user_type,staff','exists:users,id'],
            'admin_id' => ['nullable','required_if:user_type,partner','in:1,2'],
            'supplier_id' => ['nullable','required_if:user_type,supplier','exists:suppliers,id'],
            'bank_cash' => ['nullable','in:bank,cash']
        ]);

        if(!$validator->fails()){
            $params = $request->except('_token');
            $user_type = $params['user_type'];
            $bank_cash = !empty($params['bank_cash'])?$params['bank_cash']:'';
            $store_id = !empty($params['store_id'])?$params['store_id']:NULL;
            $staff_id = !empty($params['staff_id'])?$params['staff_id']:NULL;
            $admin_id = !empty($params['admin_id'])?$params['admin_id']:NULL;
            $supplier_id = !empty($params['supplier_id'])?$params['supplier_id']:NULL;
            
            $from_date = !empty($params['from_date'])?$params['from_date']:date('Y-m-01', strtotime(date('Y-m-d')));
            $to_date = !empty($params['to_date'])?$params['to_date']:date('Y-m-d');

            // dd($params);
            $data = $outstanding = array();
            $day_opening_amount = $is_opening_bal =  0;
            $non_tr_day_opening_amount = 0;
            $is_opening_bal_showable = 1;
            $opening_bal_date = "";

            $data = Ledger::select('id','user_type','transaction_id','transaction_amount','is_credit','is_debit','bank_cash','entry_date','purpose','purpose_description');
            
            $opening_bal = Ledger::select('*');

            if($user_type == 'store' && !empty($store_id)){
                $data = $data->where('user_type', 'store')->where('store_id',$store_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('store_id',$store_id);
            }else if($user_type == 'staff'  && !empty($staff_id)){
                $data = $data->where('user_type', 'staff')->where('staff_id',$staff_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('staff_id',$staff_id);
            }else if($user_type == 'partner' && !empty($admin_id)){
                $data = $data->where('user_type', 'partner')->where('admin_id',$admin_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('admin_id',$admin_id);
            }else if($user_type == 'supplier' && !empty($supplier_id)){
                $data = $data->where('user_type','supplier')->where('supplier_id',$supplier_id);
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('supplier_id',$supplier_id);
            }
            /* ++++++++CHECK OPENING BALANCE STORE+++++++++++ */
            if($user_type == 'store'){
                $check_ob_exist_store = Ledger::where('purpose','opening_balance')->where('user_type', 'store')->where('store_id',$store_id)->orderBy('id','asc')->first();

                if(!empty($check_ob_exist_store)){
                    // dd('Hi');
                    $from_date = ($params['from_date'] < $check_ob_exist_store->entry_date) ? $check_ob_exist_store->entry_date : $params['from_date'];
                    // dd('Hi:- '.$from_date);
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
            }            
            /* ++++++++CHECK OPENING BALANCE PARTNER+++++++++++ */
            if($user_type == 'partner'){
                $check_ob_exist_partner = Ledger::where('purpose','opening_balance')->where('user_type', 'partner')->where('admin_id',$admin_id)->orderBy('id','asc')->first();

                if(!empty($check_ob_exist_partner)){
                    $from_date = ($params['from_date'] < $check_ob_exist_partner->entry_date) ? $check_ob_exist_partner->entry_date : $params['from_date'];
                    $is_opening_bal = 1;
                    $opening_bal_date = $check_ob_exist_partner->entry_date;

                    if($from_date == $check_ob_exist_partner->entry_date){                    
                        $is_opening_bal_showable = 0;                    
                    } else {
                        $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_partner->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                    }                
                } else {
                    $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                } 
            }
            if(!empty($from_date) && !empty($to_date)){
                $data = $data->whereRaw("entry_date BETWEEN '".$from_date."' AND '".$to_date."' ");
            }            
            $opening_bal = $opening_bal->orderBy('entry_date','asc');  
            $opening_bal = $opening_bal->orderBy('updated_at','asc');  
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
                $data = $data->where('bank_cash', $bank_cash);
            }
            // dd($day_opening_amount);
            $data = $data->orderBy('entry_date','asc');  
            $data = $data->orderBy('updated_at','asc');  
            $data = $data->get()->toarray();  
            
            
            if(empty($data)){
                // dd('Empty');
                $non_tr_opening_bal = Ledger::select('*');
                if($user_type == 'store' && !empty($store_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'store')->where('store_id',$store_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'staff'  && !empty($staff_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'staff')->where('staff_id',$staff_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'partner' && !empty($admin_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'partner')->where('admin_id',$admin_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'supplier' && !empty($supplier_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type','supplier')->where('supplier_id',$supplier_id);
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }

                $non_tr_opening_bal = $non_tr_opening_bal->whereRaw("entry_date BETWEEN '".$started_date."' AND '".$to_date."' ")->get();

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

            /* ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            // dd($data);
            $myArr = array();
            foreach($data  as  $item){
                $myArr[] = array(
                    'is_credit' => $item['is_credit'],
                    'is_debit' => $item['is_debit'],
                    'purpose' => $item['purpose'],
                    'transaction_id' => $item['transaction_id'],
                    'transaction_amount' => $item['transaction_amount'],
                    'entry_date' => $item['entry_date'],
                    'bank_cash' => $item['bank_cash']
                ); 
                
            }

            
            
            
            if(!empty($is_opening_bal_showable)){
                $is_credit = $is_debit = 0;
                $getCrDrOB = getCrDr($day_opening_amount);
                if($getCrDrOB == 'Cr'){
                    $is_credit = 1;
                } else if($getCrDrOB == 'Dr'){
                    $is_debit = 1;
                } else if($getCrDrOB == ''){
                    
                }
                $ob_arr = array(
                    'is_credit' => $is_credit,
                    'is_debit' => $is_debit,
                    'purpose' => "Opening Balance",
                    'transaction_id' => '',
                    'transaction_amount' => replaceMinusSign($day_opening_amount),
                    'entry_date' => $from_date,
                    'bank_cash' => ''
                );

                array_unshift($myArr,$ob_arr);
                
            }

            $fileName = ucwords($user_type)."-".date('Y-m-d-H-i-s-A').".csv";
            
            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );
    
            $columns = array('Date','Transaction Id / Voucher No', 'Purpose', 'Debit', 'Credit',  'Closing');
    
            $callback = function() use($myArr, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);
                $net_value = 0;
                
                foreach ($myArr as $item) {
                    $creditAmt = $debitAmt = '';
                    if($item['is_credit'] == 1){
                        $creditAmt = $item['transaction_amount'];
                        $net_value += $item['transaction_amount'];
                    }
                    if($item['is_debit'] == 1){
                        $debitAmt = ($item['transaction_amount']);
                        $net_value -= $item['transaction_amount'];
                    }
                    // echo $net_value; die;
                    
                    $show_payment_mode = !empty($item['bank_cash']) ? "( ".ucwords($item['bank_cash'])." )" : "";
                    $row['Date']  = date('d/m/Y', strtotime($item['entry_date']));
                    $row['Transaction Id / Voucher No'] = $item['transaction_id'];
                    $row['Purpose'] = ucwords(str_replace("_"," ",$item['purpose']))." ".$show_payment_mode;                
                    $row['Debit']  = replaceMinusSign($debitAmt);
                    $row['Credit']    = $creditAmt;
                    $row['Closing']  =  replaceMinusSign($net_value)." ".getCrDr($net_value);
    
                    fputcsv($file, array($row['Date'], $row['Transaction Id / Voucher No'],$row['Purpose'], $row['Debit'], $row['Credit'], $row['Closing']));                
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);

        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
    }

    public function pdf(Request $request)
    {
        # download pdf...
        $validator = Validator::make($request->all(),[
            'from_date' => ['required', 'date','date_format:Y-m-d','before_or_equal:'.date('Y-m-d')],
            'to_date' => ['required', 'date','date_format:Y-m-d','before_or_equal:'.date('Y-m-d')],
            'user_type' => ['required', 'in:store,staff,supplier,partner'],
            'store_id' => ['nullable','required_if:user_type,store','exists:stores,id'],
            'staff_id' => ['nullable','required_if:user_type,staff','exists:users,id'],
            'admin_id' => ['nullable','required_if:user_type,partner','in:1,2'],
            'supplier_id' => ['nullable','required_if:user_type,supplier','exists:suppliers,id'],
            'bank_cash' => ['nullable','in:bank,cash']
        ]);

        if(!$validator->fails()){
            $params = $request->except('_token');
            $user_type = $params['user_type'];
            $bank_cash = !empty($params['bank_cash'])?$params['bank_cash']:'';
            $store_id = !empty($params['store_id'])?$params['store_id']:NULL;
            $staff_id = !empty($params['staff_id'])?$params['staff_id']:NULL;
            $admin_id = !empty($params['admin_id'])?$params['admin_id']:NULL;
            $supplier_id = !empty($params['supplier_id'])?$params['supplier_id']:NULL;
            
            $from_date = !empty($params['from_date'])?$params['from_date']:date('Y-m-01', strtotime(date('Y-m-d')));
            $to_date = !empty($params['to_date'])?$params['to_date']:date('Y-m-d');

            // dd($params);
            $data = $outstanding = array();
            $day_opening_amount = $is_opening_bal =  0;
            $non_tr_day_opening_amount = 0;
            $is_opening_bal_showable = 1;
            $opening_bal_date = "";

            $data = Ledger::select('id','user_type','transaction_id','transaction_amount','is_credit','is_debit','bank_cash','entry_date','purpose','purpose_description');
            
            $opening_bal = Ledger::select('*');

            if($user_type == 'store' && !empty($store_id)){
                $data = $data->where('user_type', 'store')->where('store_id',$store_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('store_id',$store_id);
            }else if($user_type == 'staff'  && !empty($staff_id)){
                $data = $data->where('user_type', 'staff')->where('staff_id',$staff_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('staff_id',$staff_id);
            }else if($user_type == 'partner' && !empty($admin_id)){
                $data = $data->where('user_type', 'partner')->where('admin_id',$admin_id);
                
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('admin_id',$admin_id);
            }else if($user_type == 'supplier' && !empty($supplier_id)){
                $data = $data->where('user_type','supplier')->where('supplier_id',$supplier_id);
                $opening_bal = $opening_bal->where('user_type',$user_type)->where('supplier_id',$supplier_id);
            }
            /* ++++++++CHECK OPENING BALANCE STORE+++++++++++ */
            if($user_type == 'store'){
                $check_ob_exist_store = Ledger::where('purpose','opening_balance')->where('user_type', 'store')->where('store_id',$store_id)->orderBy('id','asc')->first();

                if(!empty($check_ob_exist_store)){
                    // dd('Hi');
                    $from_date = ($params['from_date'] < $check_ob_exist_store->entry_date) ? $check_ob_exist_store->entry_date : $params['from_date'];
                    // dd('Hi:- '.$from_date);
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
            }            
            /* ++++++++CHECK OPENING BALANCE PARTNER+++++++++++ */
            if($user_type == 'partner'){
                $check_ob_exist_partner = Ledger::where('purpose','opening_balance')->where('user_type', 'partner')->where('admin_id',$admin_id)->orderBy('id','asc')->first();

                if(!empty($check_ob_exist_partner)){
                    $from_date = ($params['from_date'] < $check_ob_exist_partner->entry_date) ? $check_ob_exist_partner->entry_date : $params['from_date'];
                    $is_opening_bal = 1;
                    $opening_bal_date = $check_ob_exist_partner->entry_date;

                    if($from_date == $check_ob_exist_partner->entry_date){                    
                        $is_opening_bal_showable = 0;                    
                    } else {
                        $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_partner->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                    }                
                } else {
                    $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
                } 
            }
            if(!empty($from_date) && !empty($to_date)){
                $data = $data->whereRaw("entry_date BETWEEN '".$from_date."' AND '".$to_date."' ");
            }            
            $opening_bal = $opening_bal->orderBy('entry_date','asc');  
            $opening_bal = $opening_bal->orderBy('updated_at','asc');  
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
                $data = $data->where('bank_cash', $bank_cash);
            }
            // dd($day_opening_amount);
            $data = $data->orderBy('entry_date','asc');  
            $data = $data->orderBy('updated_at','asc');  
            $data = $data->get();  
            
            
            if(empty($data)){
                // dd('Empty');
                $non_tr_opening_bal = Ledger::select('*');
                if($user_type == 'store' && !empty($store_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'store')->where('store_id',$store_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'staff'  && !empty($staff_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'staff')->where('staff_id',$staff_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'partner' && !empty($admin_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type', 'partner')->where('admin_id',$admin_id);
                    
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }else if($user_type == 'supplier' && !empty($supplier_id)){
                    $non_tr_opening_bal = $non_tr_opening_bal->where('user_type','supplier')->where('supplier_id',$supplier_id);
                    $started_date = Ledger::where('user_type',$user_type)->where('store_id',$store_id)->min('entry_date');
                }

                $non_tr_opening_bal = $non_tr_opening_bal->whereRaw("entry_date BETWEEN '".$started_date."' AND '".$to_date."' ")->get();

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
            
            $ledgerpdfname = ucwords($user_type)."-".date('Y-m-d-H-i-s-A')."";

            // dd($data);
            $select_user_name = '';
            if($user_type == 'store'){
                $select_user_name = getSingleAttributeTable('stores',$store_id,'bussiness_name');
            } else if ($user_type == 'staff'){
                $select_user_name = getSingleAttributeTable('users',$staff_id,'name');
            } else if ($user_type == 'partner'){
                $select_user_name = getSingleAttributeTable('users',$admin_id,'name');
            } else if ($user_type == 'supplier'){
                $select_user_name = getSingleAttributeTable('suppliers',$supplier_id,'name');
            }
            

            $pdf = Pdf::loadView('admin.report.ledger-pdf', compact('data','user_type','store_id','staff_id','admin_id','supplier_id','select_user_name','from_date','to_date','day_opening_amount','is_opening_bal','is_opening_bal_showable','opening_bal_date','bank_cash'));
            return $pdf->download($ledgerpdfname.'.pdf');

        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
    }

    private function getUserName($type,$id){
        if($type == 'store'){
            $name = getSingleAttributeTable('stores',$id,'bussiness_name');
        } else if ($type == 'staff'){
            $name = getSingleAttributeTable('users',$id,'name');
        } else if ($type == 'partner'){
            $name = getSingleAttributeTable('users',$id,'name');
        } else if ($type == 'supplier'){
            $name = getSingleAttributeTable('suppliers',$id,'name');
        }
    }
}
