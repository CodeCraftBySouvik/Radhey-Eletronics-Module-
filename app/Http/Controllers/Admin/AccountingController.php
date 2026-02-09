<?php







namespace App\Http\Controllers\Admin;

use Carbon\Carbon;






use App\Http\Controllers\Controller;



use Illuminate\Http\Request;



use Illuminate\Support\Facades\DB;



use Illuminate\Support\Facades\Session;



use Illuminate\Support\Facades\Auth;



use App\Models\PaymentCollection;



use App\Models\Expense;



use App\Models\Payment;
use App\Models\Order;


use App\Models\Ledger;



use App\Models\Journal;



use App\Models\Invoice;



use App\Models\InvoicePayment;



use App\Models\StaffCommision;



use App\Models\Store;



use App\Models\PaymentUpdate;



use App\Models\Changelog;



use App\User;



use App\Models\UserCity;



use App\Models\StoreBadDebt;



use App\Exports\ExpensesExport;
use Maatwebsite\Excel\Facades\Excel;



class AccountingController extends Controller



{



    public function __construct(Request $request)



    {



        $this->middleware('auth:web');



        $this->middleware(function ($request, $next) {



            $this->id = Auth::user()->id;



            $this->type = Auth::user()->type;



            $this->designation = Auth::user()->designation;



            // dd($this->type);



            if($this->type == 2){



                // $userAccesses = userAccesses($this->designation,10);



                $userAccesses = ($this->designation == 3 || $this->designation == 2)?true:false;  # Head 



                if(!$userAccesses){



                    abort(401);



                }



            }







            return $next($request);



        });



    }







    public function add_opening_balance(Request $request,$type='')



    {



        if(!empty($type) && $type == 'partner'){



            $store = DB::table('users')->select('id','name','mobile','email')->where('type',1)->where('status',1)->get();



        }



        $store = DB::table('stores')->select('id','store_name')->where('status', 1)->get();



        return view('admin.accounting.add_openning_balance', compact('store','type'));



    }







    public function save_opening_balance(Request $request)



    {



        $type_val = $request->type_val;



        $request->validate([



            'store_id' => 'required',



            'payment_date' => 'required', 



            'payment_in' => 'required', 



            'payment_mode' => 'required', 



            'amount' => 'required_if:payment_in,bank,cash',



            'bank_amount' => 'required_if:payment_in,bank_n_cash',



            'cash_amount' => 'required_if:payment_in,bank_n_cash',



            'payment_for' => 'required'



        ],[



            'store_id.required' => "Please choose store",



            'payment_date.required' => "Please add date of payment",



            'payment_in' => "Please mention way of payment",



            'payment_mode' => "Please mention mode of payment",



            'amount.required_if' => "Please add amount",   



            'bank_amount.required_if' => "Please add bank amount",   



            'cash_amount.required_if' => "Please add cash amount",          



            'payment_for.required' => "Please mention type of payment for"



        ]);    



                



        $is_credit = 0;



        $is_debit = 0;



        if($request->payment_for == 'credit'){



            $is_credit = 1;            



        }



        if($request->payment_for == 'debit'){



            $is_debit = 1;            



        }







        $payment_in = $request->payment_in;







        if(!empty($request->store_id) ){



            # Check exist opening balance for store with payment_in







            $existOB = DB::table('ledger')->select('ledger.*','p.payment_in','p.bank_cash','p.payment_mode')->leftJoin('payment AS p','p.id','ledger.payment_id')->where('ledger.store_id',$request->store_id)->where('ledger.user_type','store')->where('ledger.purpose','opening_balance')->get()->toarray();







            // dd($existOB);







            if(!empty($existOB)){



                // dd($existOB);



                # restrict previous date of existing OB date



                if($request->payment_date < $existOB[0]->entry_date) {



                    $err_msg_date = "Previous date (".date('d/m/Y', strtotime($existOB[0]->entry_date)).") of your existing opening balance is not allowed ";



                    return  redirect()->back()->withErrors(['payment_date'=> $err_msg_date])->withInput();



                }



                



                # check bank and cash entry exists



                foreach($existOB as $ob){



                    if(in_array($request->payment_in, array("bank","bank_n_cash")) &&  $ob->bank_cash == 'bank'){



                        $err_msg_bank = "Already bank entry exists";



                        return  redirect()->back()->withErrors(['payment_in'=> $err_msg_bank])->withInput();



                    }



                    if(in_array($request->payment_in, array("cash","bank_n_cash")) &&  $ob->bank_cash == 'cash'){



                        $err_msg_bank = "Already cash entry exists";



                        return  redirect()->back()->withErrors(['payment_in'=> $err_msg_bank])->withInput();



                    }



                }                



            }            



        }           



        /* For store opening balance */



        $store_id = $request->store_id;       



        $user_type = 'store';        



        # add OB at the top of the existing transaction of the day



        if($request->payment_in == 'bank_n_cash'){



            if(isset($request->bank_amount)) {



                /* Entry in payment */



                $payment_id = Payment::insertGetId([



                    'store_id' => $store_id,



                    'payment_for' => $request->payment_for,



                    'voucher_no' => $request->voucher_no,



                    'payment_date' => $request->payment_date,



                    'payment_in' => $request->payment_in,



                    'bank_cash' => 'bank',



                    'payment_mode' => $request->payment_mode, # cheque or neft



                    'amount' => $request->bank_amount,



                    'chq_utr_no' => $request->chq_utr_no,



                    'bank_name' => $request->bank_name,



                    'narration' => $request->narration,



                    'created_by' => Auth::user()->id



                ]);



                /* Entry in ledger */



                Ledger::insert([



                    'user_type' => $user_type,



                    'store_id' => $store_id,



                    'transaction_id' => $request->voucher_no,



                    'transaction_amount' => $request->bank_amount,



                    'bank_cash' => 'bank',



                    'is_credit' => $is_credit,



                    'is_debit' => $is_debit,



                    'entry_date' => $request->payment_date,



                    'payment_id' => $payment_id,



                    'purpose' => 'opening_balance',



                    'purpose_description' => $user_type." opening balance",



                    'updated_at' => '0000-00-00 00:00:00'



                ]);



                /* Entry in journal */



                Journal::insert([



                    'transaction_amount' => $request->bank_amount,



                    'is_credit' => $is_credit,



                    'is_debit' => $is_debit,



                    'entry_date' => $request->payment_date,



                    'payment_id' => $payment_id,



                    'bank_cash' => 'bank',



                    'purpose' => 'opening_balance',



                    'purpose_description' => $user_type." opening balance",



                    'purpose_id' => $request->voucher_no,



                    'updated_at' => '0000-00-00 00:00:00'



                ]);



            }



            if(isset($request->cash_amount)) {



                /* Entry in payment */



                $payment_id = Payment::insertGetId([



                    'store_id' => $store_id,



                    'payment_for' => $request->payment_for,



                    'voucher_no' => $request->voucher_no,



                    'payment_date' => $request->payment_date,



                    'payment_in' => $request->payment_in,



                    'bank_cash' => 'cash',



                    'payment_mode' => 'cash', # cheque or neft or cash



                    'amount' => $request->cash_amount,



                    'chq_utr_no' => '',



                    'bank_name' => '',



                    'narration' => $request->narration,



                    'created_by' => Auth::user()->id



                ]);



                /* Entry in ledger */



                Ledger::insert([



                    'user_type' => $user_type,



                    'store_id' => $store_id,



                    'transaction_id' => $request->voucher_no,



                    'transaction_amount' => $request->cash_amount,



                    'is_credit' => $is_credit,



                    'is_debit' => $is_debit,



                    'entry_date' => $request->payment_date,



                    'payment_id' => $payment_id,



                    'bank_cash' => 'cash',



                    'purpose' => 'opening_balance',



                    'purpose_description' => $user_type." opening balance",



                    'updated_at' => '0000-00-00 00:00:00'



                ]);



                /* Entry in journal */



                Journal::insert([



                    'transaction_amount' => $request->cash_amount,



                    'is_credit' => $is_credit,



                    'is_debit' => $is_debit,



                    'entry_date' => $request->payment_date,



                    'payment_id' => $payment_id,



                    'bank_cash' => 'cash',



                    'purpose' => 'opening_balance',



                    'purpose_description' => $user_type." opening balance",



                    'purpose_id' => $request->voucher_no,



                    'updated_at' => '0000-00-00 00:00:00'



                ]);



            }



        } else {



            /* Entry in payment */            



            $payment_id = Payment::insertGetId([



                'store_id' => $store_id,



                'payment_for' => $request->payment_for,



                'voucher_no' => $request->voucher_no,



                'payment_date' => $request->payment_date,



                'payment_in' => $request->payment_in,    



                'bank_cash' => ($request->payment_in != 'bank') ? 'cash':  $request->payment_in,            



                'payment_mode' => $request->payment_mode,



                'amount' => $request->amount,



                'chq_utr_no' => $request->chq_utr_no,



                'bank_name' => $request->bank_name,



                'narration' => $request->narration,



                'created_by' => Auth::user()->id



            ]);



            /* Entry in ledger */



            Ledger::insert([



                'user_type' => $user_type,



                'store_id' => $store_id,



                'transaction_id' => $request->voucher_no,



                'transaction_amount' => $request->amount,



                'is_credit' => $is_credit,



                'is_debit' => $is_debit,



                'entry_date' => $request->payment_date,



                'payment_id' => $payment_id,



                'bank_cash' => ($request->payment_in != 'bank') ? 'cash':  $request->payment_in,  



                'purpose' => 'opening_balance',



                'purpose_description' => $user_type." opening balance",



                'updated_at' => '0000-00-00 00:00:00'



            ]);



            /* Entry in journal */



            Journal::insert([



                'transaction_amount' => $request->amount,



                'is_credit' => $is_credit,



                'is_debit' => $is_debit,



                'entry_date' => $request->payment_date,



                'bank_cash' => ($request->payment_in != 'bank') ? 'cash':  $request->payment_in,  



                'purpose' => 'opening_balance',



                'purpose_description' => $user_type." opening balance",



                'purpose_id' => $request->voucher_no,



                'payment_id' => $payment_id,



                'updated_at' => '0000-00-00 00:00:00'



            ]);



        }               







        Session::flash('message', 'Opening balance for customer added successfully'); 



        return redirect()->route('admin.accounting.listopeningbalance');        



    }







    public function save_opening_balance_partner(Request $request)



    {



        # save opening balance for partner...



        $request->validate([



            'admin_id' => 'required',



            'payment_date' => 'required', 



            'payment_in' => 'required', 



            'payment_mode' => 'required', 



            'amount' => 'required_if:payment_in,bank,cash',



            'bank_amount' => 'required_if:payment_in,bank_n_cash',



            'cash_amount' => 'required_if:payment_in,bank_n_cash',



            'payment_for' => 'required'



        ],[



            'admin_id.required' => "Please choose partner",



            'payment_date.required' => "Please add date of payment",



            'payment_in' => "Please mention way of payment",



            'payment_mode' => "Please mention mode of payment",



            'amount.required_if' => "Please add amount",  



            'bank_amount.required_if' => "Please add bank amount",   



            'cash_amount.required_if' => "Please add cash amount",        



            'payment_for.required' => "Please mention type of payment for"



        ]);







        $is_credit = 0;



        $is_debit = 0;



        if($request->payment_for == 'credit'){



            $is_credit = 1;            



        }



        if($request->payment_for == 'debit'){



            $is_debit = 1;            



        }



        $payment_in = $request->payment_in;







        if(!empty($request->admin_id) ){



            /* Check exist opening balance for partner with payment_in */



            $existOB = DB::table('ledger')->select('ledger.*','p.payment_in','p.bank_cash','p.payment_mode')->leftJoin('payment AS p','p.id','ledger.payment_id')->where('ledger.admin_id',$request->admin_id)->where('ledger.user_type','partner')->where('ledger.purpose','opening_balance')->get()->toarray();







            if(!empty($existOB)){



                // dd($existOB);



                # restrict previous date of existing OB date



                if($request->payment_date < $existOB[0]->entry_date) {



                    $err_msg_date = "Previous date (".date('d/m/Y', strtotime($existOB[0]->entry_date)).") of your existing opening balance is not allowed ";



                    return  redirect()->back()->withErrors(['payment_date'=> $err_msg_date])->withInput();



                }                



                # check bank exists only



                foreach($existOB as $ob){



                    if(in_array($request->payment_in, array("bank","bank_n_cash")) &&  $ob->bank_cash == 'bank'){



                        $err_msg_bank = "Already bank entry exists";



                        return  redirect()->back()->withErrors(['payment_in'=> $err_msg_bank])->withInput();



                    }



                    if(in_array($request->payment_in, array("cash","bank_n_cash")) &&  $ob->bank_cash == 'cash'){



                        $err_msg_bank = "Already cash entry exists";



                        return  redirect()->back()->withErrors(['payment_in'=> $err_msg_bank])->withInput();



                    }



                }                                



            }           



        }          



        /* For partner opening balance */



        $admin_id = $request->admin_id;        



        $user_type = 'partner';



        if($request->payment_in == 'bank_n_cash'){



            if(isset($request->bank_amount)) {



                /* Entry in payment */



                $payment_id = Payment::insertGetId([



                    'admin_id' => $admin_id,



                    'payment_for' => $request->payment_for,



                    'voucher_no' => $request->voucher_no,



                    'payment_date' => $request->payment_date,



                    'payment_in' => $request->payment_in,



                    'bank_cash' => 'bank',



                    'payment_mode' => $request->payment_mode, # cheque or neft



                    'amount' => $request->bank_amount,



                    'chq_utr_no' => $request->chq_utr_no,



                    'bank_name' => $request->bank_name,



                    'narration' => $request->narration,



                    'created_by' => Auth::user()->id   



                ]);



                /* Entry in ledger */



                Ledger::insert([



                    'user_type' => $user_type,



                    'admin_id' => $admin_id,



                    'transaction_id' => $request->voucher_no,



                    'transaction_amount' => $request->bank_amount,



                    'is_credit' => $is_credit,



                    'is_debit' => $is_debit,



                    'entry_date' => $request->payment_date,



                    'payment_id' => $payment_id,



                    'bank_cash' => 'bank',



                    'purpose' => 'opening_balance',



                    'purpose_description' => $user_type." opening balance",



                    'updated_at' => '0000-00-00 00:00:00'



                ]);



                



            }



            if(isset($request->cash_amount)) {



                /* Entry in payment */



                $payment_id = Payment::insertGetId([



                    'admin_id' => $admin_id,



                    'payment_for' => $request->payment_for,



                    'voucher_no' => $request->voucher_no,



                    'payment_date' => $request->payment_date,



                    'payment_in' => $request->payment_in,



                    'bank_cash' => 'cash',



                    'payment_mode' => 'cash', # cheque or neft or cash



                    'amount' => $request->cash_amount,



                    'chq_utr_no' => '',



                    'bank_name' => '',



                    'narration' => $request->narration,



                    'created_by' => Auth::user()->id



                ]);



                /* Entry in ledger */



                Ledger::insert([



                    'user_type' => $user_type,



                    'admin_id' => $admin_id,



                    'transaction_id' => $request->voucher_no,



                    'transaction_amount' => $request->cash_amount,



                    'is_credit' => $is_credit,



                    'is_debit' => $is_debit,



                    'entry_date' => $request->payment_date,



                    'payment_id' => $payment_id,



                    'bank_cash' => 'cash',



                    'purpose' => 'opening_balance',



                    'purpose_description' => $user_type." opening balance",



                    'updated_at' => '0000-00-00 00:00:00'



                ]);



                



            }



        } else {



            /* Entry in payment */



            $payment_id = Payment::insertGetId([



                'admin_id' => $admin_id,



                'payment_for' => $request->payment_for,



                'voucher_no' => $request->voucher_no,



                'payment_date' => $request->payment_date,



                'payment_in' => $request->payment_in,



                'bank_cash' => ($request->payment_in != 'bank') ? 'cash' : $request->payment_in,



                'payment_mode' => $request->payment_mode,



                'amount' => $request->amount,



                'chq_utr_no' => $request->chq_utr_no,



                'bank_name' => $request->bank_name,



                'narration' => $request->narration,



                'created_by' => Auth::user()->id



            ]);



            /* Entry in ledger */



            Ledger::insert([



                'user_type' => $user_type,



                'admin_id' => $admin_id,



                'transaction_id' => $request->voucher_no,



                'transaction_amount' => $request->amount,



                'is_credit' => $is_credit,



                'is_debit' => $is_debit,



                'entry_date' => $request->payment_date,



                'payment_id' => $payment_id,



                'bank_cash' => ($request->payment_in != 'bank') ? 'cash' : $request->payment_in,



                'purpose' => 'opening_balance',



                'purpose_description' => $user_type." opening balance",



                'updated_at' => '0000-00-00 00:00:00'



            ]);



            



        }               



        Session::flash('message', 'Opening balance for partner added successfully');        



        return redirect()->route('admin.accounting.add-opening-balance','partner');



    }







    ## Add Depot Expense Start ##



    public function add_expenses(Request $request)



    {        



        return view('admin.accounting.add_expense');



    }


    public function save_expenses(Request $request)
    { 
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $staff_id = !empty($request->staff_id)?$request->staff_id:'';
        $admin_id = !empty($request->admin_id)?$request->admin_id:'';
        $supplier_id = !empty($request->supplier_id)?$request->supplier_id:'';
        $user_type = !empty($request->user_type)?$request->user_type:'';
        $expense_id = !empty($request->expense_id)?$request->expense_id:'';

        if($user_type != 'miscellaneous'){
            $request->validate([
                'payment_date' => 'required', 
                'payment_mode' => 'required', 
                'amount' => 'required', 
                'user_type' => 'required',
                'user_id' => 'required',
                'user_name' => 'required',
                'expense_id' => 'required'
            ],[

                'payment_date.required' => "Please add date of payment",
                'payment_mode' => "Please mention mode of payment",
                'amount.required' => "Please add amount",
                'user_type.required' => "Please mention expense at",
                'user_id.required' => "Please specify which user",
                'expense_id.required' => "Please add expense type"
            ]);
        }else{
            $request->validate([
                'payment_date' => 'required', 
                'payment_mode' => 'required', 
                'amount' => 'required', 
                'user_type' => 'required'                
            ],[
                'payment_date.required' => "Please add date of payment",
                'payment_mode' => "Please mention mode of payment",
                'amount.required' => "Please add amount",
                'user_type.required' => "Please mention expense at",                
            ]);
        }

        $paymentData = array(            
            'payment_for' => 'debit',
            'voucher_no' => $request->voucher_no,
            'payment_date' => $request->payment_date,
            'payment_mode' => $request->payment_mode,
            'payment_in' => ($request->payment_mode != 'cash') ? 'bank' : 'cash' ,
            'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank', 
            'amount' => $request->amount,
            'bank_name' => $request->bank_name,
            'is_ledger_added' => 1,
            'is_approved' => 1,
            'chq_utr_no' => $request->chq_utr_no,
            'narration' => $request->narration,
            'created_by' => Auth::user()->id
        );        

        if($user_type == 'miscellaneous'){

        } else {
            if($user_type == 'staff'){
                $paymentStaff = array('staff_id' => $staff_id);
                $paymentData = array_merge($paymentData,$paymentStaff);
            } else if ($user_type == 'store'){
                $paymentStore = array('store_id' => $store_id);
                $paymentData = array_merge($paymentData,$paymentStore);
            } else if ($user_type == 'partner'){
                $paymentAdmin = array('admin_id' => $admin_id);
                $paymentData = array_merge($paymentData,$paymentAdmin);
            } else if ($user_type == 'supplier'){
                $paymentSupplier = array('supplier_id' => $supplier_id);
                $paymentData = array_merge($paymentData,$paymentSupplier);
            }
        }        

        if(!empty($expense_id)){
            $paymentExpense = array('expense_id' => $expense_id);
            $paymentData = array_merge($paymentData,$paymentExpense);
        }

        $payment_id = Payment::insertGetId($paymentData);        

        $is_credit = 0; 

        $is_debit = 1;

        /* Add expense in purpose */
        $expense_name = "";

        if(!empty($expense_id)){

            $expense_name = !empty($request->expense_name)?$request->expense_name:'';

        }

        $purpose_description = "expense for ".$user_type.". ".$expense_name;

        /* Add Contra Entry As Credit For Staff */

        if($user_type == 'staff' && !empty($staff_id)){

            $checkExpense = DB::table('expense')->find($expense_id);

            if(!empty($checkExpense)){

                if(!empty($checkExpense->for_credit)){

                    $staffCredLedgerArr = array(

                        'user_type' => $user_type,
                        'staff_id' => $staff_id,
                        'transaction_id' => 'STAFFEXPENSE'.time(),
                        'transaction_amount' => $request->amount,
                        'payment_id' => $payment_id,
                        'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank', 
                        'is_credit' => 1,
                        'entry_date' => $request->payment_date,
                        'purpose' => 'staff_expense',
                        'purpose_description' => "Contra Entry For ".$expense_name.""
                    );
                    Ledger::insert($staffCredLedgerArr);
                }
            }
        }

        /* End Contra Entry As Credit For Staff  */
        /* ====================== */
        if($user_type != 'miscellaneous'){

            $ledgerData = array(

                'user_type' => $user_type,
                'transaction_id' => $request->voucher_no,
                'transaction_amount' => $request->amount,
                'payment_id' => $payment_id,
                'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank', 
                'is_credit' => $is_credit,
                'is_debit' => $is_debit,
                'entry_date' => $request->payment_date,
                'purpose' => 'expense',
                'purpose_description' => $purpose_description
            );

            if($user_type == 'staff'){
                $ledgerStaff = array('staff_id' => $staff_id);
                $ledgerData = array_merge($ledgerData,$ledgerStaff);
            } else if ($user_type == 'store'){
                $ledgerStore = array('store_id' => $store_id);
                $ledgerData = array_merge($ledgerData,$ledgerStore);
            } else if ($user_type == 'partner'){
                $ledgerAdmin = array('admin_id' => $admin_id);
                $ledgerData = array_merge($ledgerData,$ledgerAdmin);
            } else if ($user_type == 'supplier'){
                $ledgerSupplier = array('supplier_id' => $supplier_id);
                $ledgerData = array_merge($ledgerData,$ledgerSupplier);
            }

            // dd($ledgerData);            
            Ledger::insert($ledgerData);
        }        

        /* Entry in journal */
        Journal::insert([
            'transaction_amount' => $request->amount,
            'is_credit' => $is_credit,
            'is_debit' => $is_debit,
            'entry_date' => $request->payment_date,
            'payment_id' => $payment_id,
            'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank', 
            'purpose' => 'expense',
            'purpose_description' =>  $purpose_description ,
            'purpose_id' => $request->voucher_no
        ]);

        /* for partner withdrawl */
        $withdrawls_id = !empty($request->withdrawls_id)?$request->withdrawls_id:'';

        if(!empty($withdrawls_id)){
            DB::table('withdrawls')->where('id',$withdrawls_id)->update(['is_disbursed' => 1]);
        }        

        if(!empty($withdrawls_id)){
            $successMsg = "Withdrawl disbursed for partner successfully";

            Session::flash('message', $successMsg); 
            return redirect()->route('admin.revenue.withdrawls');
        } else {

            $successMsg = "Expense added successfully for ".$user_type."";

            Session::flash('message', $successMsg); 

            return redirect()->route('admin.accounting.add_expenses');
        }
    }

    ## Add Depot Expense End ##


    ## Add Partner Expense Start ##



    public function add_partner_expense(Request $request)



    {



        // dd('Hi');



        $expense_types = Expense::where('for_partner', 1)->where('for_credit', 1)->where('status', 1)->orderBy('title')->get();



        // dd($expense_types);



        return view('admin.accounting.add_partner_expense', compact('expense_types'));



    }




     public function save_partner_expense(Request $request)
    {
         $request->validate([

            'amount' => 'required',



            'payment_date' => 'required',



            'payment_mode' => 'required',



            'chq_utr_no' => 'required_unless:payment_mode,cash',



            'bank_name' => 'required_unless:payment_mode,cash',



            'expense_id' => 'required'



        ],[



            'amount.required' => 'Please add amount',



            'payment_date.required' => 'Please add date',



            'payment_mode.required' => 'Please add mode of payment',



            'chq_utr_no.required_unless' => 'Please add Cheque No or UTR No',



            'bank_name.required_unless' => 'Please add bank name',



            'expense_id.required' => 'Please add expense'



        ]);

        DB::beginTransaction();
        try {

            $createdFrom = $request->created_from ?? 'web'; // default admin

            $paymentData = [
                'admin_id' => $request->admin_id,
                'voucher_no' => $request->voucher_no,
                'expense_id' => $request->expense_id,
                'payment_for' => 'debit',
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
                'payment_in' => ($request->payment_mode != 'cash') ? 'bank' : 'cash',
                'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank',
                'amount' => $request->amount,
                'bank_name' => $request->bank_name,
                'chq_utr_no' => $request->chq_utr_no,
                'narration' => $request->narration,
                'created_from' => $createdFrom,
                'is_ledger_added' => ($createdFrom == 'web') ? 1 : 0,
                'is_approved' => ($createdFrom == 'web') ? 1 : 0,
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $payment_id = Payment::insertGetId($paymentData);

            /** 🔥 AUTO APPROVE ONLY IF ADMIN */
            if ($createdFrom == 'web') {
                $this->insertExpenseLedger($payment_id);
            }

            DB::commit();
            return redirect()->route('admin.accounting.add_partner_expense')->with('message', 
                $createdFrom == 'web'
                ? 'Expense added successfully'
                : 'Expense submitted for admin approval'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors($e->getMessage());
        }
    }

    ## Extract Ledger logic (REUSABLE & CLEAN)  ##
    private function insertExpenseLedger($payment_id){
        $payment = Payment::findOrFail($payment_id);

         if ($payment->is_ledger_added == 1) {
            return;
        }

        $expense_name = getSingleAttributeTable('expense', $payment->expense_id, 'title');
         Ledger::insert([
             'user_type' => 'partner',
             'admin_id'  => $payment->admin_id,
             'transaction_id' => $payment->voucher_no,
             'transaction_amount' => $payment->amount,
             'payment_id'  => $payment->id,
             'bank_cash'  => ($payment->payment_mode == 'cash') ? 'cash' : 'bank',
             'is_credit' => 1,
             'entry_date' => $payment->payment_date,
             'purpose' => $payment->partner_expense,
               'purpose_description' => 'Partner Expense - '.$expense_name,
            'created_at' => now(),
            'updated_at' => now(),
         ]);

          Journal::insert([
            'transaction_amount' => $payment->amount,
            'is_debit' => 1,
            'entry_date' => $payment->payment_date,
            'payment_id' => $payment->id,
            'bank_cash'  => ($payment->payment_mode == 'cash') ? 'cash' : 'bank',
            'purpose' => 'partner_expense',
            'purpose_description' => $expense_name,
            'purpose_id' => $payment->voucher_no,
        ]);

        Payment::where('id', $payment_id)->update([
            'is_ledger_added' => 1,
            'is_approve' => 1,
            'updated_at' => now(),
        ]);

    }



    // public function save_partner_expense(Request $request)
    // {
    //     $request->validate([

    //         'amount' => 'required',



    //         'payment_date' => 'required',



    //         'payment_mode' => 'required',



    //         'chq_utr_no' => 'required_unless:payment_mode,cash',



    //         'bank_name' => 'required_unless:payment_mode,cash',



    //         'expense_id' => 'required'



    //     ],[



    //         'amount.required' => 'Please add amount',



    //         'payment_date.required' => 'Please add date',



    //         'payment_mode.required' => 'Please add mode of payment',



    //         'chq_utr_no.required_unless' => 'Please add Cheque No or UTR No',



    //         'bank_name.required_unless' => 'Please add bank name',



    //         'expense_id.required' => 'Please add expense'



    //     ]);



    //     $params = $request->except('_token');



    //     // dd($params);



    //     $paymentData = array(



    //         'admin_id' => $params['admin_id'],



    //         'voucher_no' => $params['voucher_no'],



    //         'expense_id' => $params['expense_id'],



    //         'payment_for' => 'credit',



    //         'payment_date' => $params['payment_date'],



    //         'payment_mode' => $params['payment_mode'],



    //         'payment_in' => ($params['payment_mode'] != 'cash') ? 'bank' : 'cash' ,



    //         'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 



    //         'amount' => $params['amount'],



    //         'bank_name' => $params['bank_name'],



    //         'chq_utr_no' => $params['chq_utr_no'],



    //         'narration' => $params['narration'],

    //         'is_ledger_added' => ($createdFrom == 'web') ? 1 : 0,
    //         'is_approve'      => ($createdFrom == 'web') ? 1 : 0,

    //         'created_by' => Auth::user()->id,



    //         'created_at' => date('Y-m-d H:i:s'),



    //         'updated_at' => date('Y-m-d H:i:s')



    //     ); 



    //     $payment_id = Payment::insertGetId($paymentData);



    //     $expense_name = getSingleAttributeTable('expense',$params['expense_id'],'title');



    //     $purpose_description = "expense for partner. ".$expense_name;



    //     $ledgerData = array(



    //         'user_type' => 'partner',



    //         'admin_id' => $params['admin_id'],



    //         'transaction_id' => $params['voucher_no'],



    //         'transaction_amount' => $params['amount'],



    //         'payment_id' => $payment_id,



    //         'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank',



    //         'is_credit' => 1,



    //         'entry_date' => $params['payment_date'],



    //         'purpose' => 'partner_expense',



    //         'purpose_description' => $purpose_description,



    //         'created_at' => date('Y-m-d H:i:s'),



    //         'updated_at' => date('Y-m-d H:i:s')



    //     );



    //     Ledger::insert($ledgerData);



        



    //     $successMsg = "Partner Expense added successfully";



    //     Session::flash('message', $successMsg); 



    //     return redirect()->route('admin.accounting.add_partner_expense');



        



    // }



    ## Add Partner Expense End ##

    





    ## Edit Partner Expense Start ##



    public function edit_partner_expense($id,Request $request)



    {



        # edit partner expense form...



        $payment = Payment::find($id);



        // dd($payment);



        return view('admin.accounting.edit_partner_expense', compact('id','payment'));



    }    







    public function update_partner_expense($id,Request $request)



    {



        # update partner expense...



        $request->validate([



            'amount' => 'required',



            'payment_in' => 'required',



            'chq_utr_no' => 'required_if:payment_in,bank',



            'bank_name' => 'required_if:payment_in,bank'



        ]);



        $params = $request->except('_token');



        $params['updated_at'] = date('Y-m-d H:i:s');



        unset($params['bank_name_hidden']);



        if($params['payment_in'] == 'cash'){



            $params['bank_name'] = '';



            $params['chq_utr_no'] = '';



        }



        #1: Payment update



        Payment::where('id',$id)->update($params);



        #2: Ledger update



        Ledger::where('payment_id',$id)->update([



            'bank_cash' => $params['bank_cash'],



            'entry_date' => $params['payment_date'],



            'transaction_amount' => $params['amount'],



            'updated_at' => $params['updated_at']



        ]);



        







        Session::flash('message', "Partner Expense Updated Successfully"); 



        return redirect()->route('admin.accounting.edit_partner_expense',$id);



    }



    ## Edit Partner Expense End ##











    ## List Depot Expense ##



    public function list_expenses(Request $request)



    {



        # List Depot Expense ...



        $paginate = 20;



        $search = !empty($request->search)?$request->search:'';



        $entry_date = !empty($request->entry_date)?$request->entry_date:'';



        $data = Payment::where('voucher_no', 'LIKE', 'EXPENSE%');



        $countData = Payment::where('voucher_no', 'LIKE', 'EXPENSE%');



        



        if(!empty($search)){



            $data = $data->where('voucher_no', 'LIKE','%'.$search.'%')->orWhere('narration','LIKE','%'.$search.'%')->orWhereHas('creator', function($cr) use($search){



                $cr->where('name', 'LIKE','%'.$search.'%');



            });



            $countData = $countData->where('voucher_no', 'LIKE','%'.$search.'%')->orWhere('narration','LIKE','%'.$search.'%')->orWhereHas('creator', function($cr) use($search){



                $cr->where('name', 'LIKE','%'.$search.'%');



            });



        }



        if(!empty($entry_date)){



            $data = $data->where('payment_date',$entry_date);



            $countData = $countData->where('payment_date',$entry_date);



        }



        



        $data = $data->orderBy('payment_date','desc')->paginate($paginate);



        $countData = $countData->count();







        $data = $data->appends([



            'search'=>$search,



            'entry_date'=>$entry_date,



            'page'=>$request->page



        ]);



       



        return view('admin.accounting.list_expense', compact('data','countData','paginate','search','entry_date'));



    }

    public function get_expense_details($payment_id){
        $payment = Payment::with(
            'staff',
            'store',
            'supplier',
            'partner',
            'expense',
            'creator'
        )->findOrFail($payment_id);

        return view('admin.accounting.expense_details',compact('payment'));
    }
    

    ## Approve Expense From Admin ##
   public function approve_expense($id)
{

    DB::beginTransaction();
    try {

        $payment = Payment::findOrFail($id);
        
        if ($payment->is_ledger_added == 1) {
            return back()->with('message', 'Expense already approved');
        }

        $expense_name = getSingleAttributeTable('expense', $payment->expense_id, 'title');
        
       $ledgerData = [
            'user_type' => $payment->staff_id ? 'staff' : ($payment->store_id ? 'store' : ($payment->admin_id ? 'partner' : 'supplier')),
            
            'transaction_id' => $payment->voucher_no,
            'transaction_amount' => $payment->amount,
            'payment_id' => $payment->id,
            'bank_cash' => $payment->bank_cash,
            'is_debit' => 1,
            'entry_date' => $payment->payment_date,
            'purpose' => 'partner_expense',
            'purpose_description' => 'Expense for partner - '.$expense_name,
            'created_at' => now(),
            'updated_at' => now()
        ];

            if ($payment->staff_id) $ledgerData['staff_id'] = $payment->staff_id;
            if ($payment->store_id) $ledgerData['store_id'] = $payment->store_id;
            if ($payment->admin_id) $ledgerData['admin_id'] = $payment->admin_id;
            if ($payment->supplier_id) $ledgerData['supplier_id'] = $payment->supplier_id;

         Ledger::insert($ledgerData);

        Journal::insert([
            'transaction_amount' => $payment->amount,
            'is_debit' => 1,
            'entry_date' => $payment->payment_date,
            'payment_id' => $payment->id,
            'bank_cash' => $payment->bank_cash,
            'purpose' => 'partner_expense',
            'purpose_description' => $expense_name,
            'purpose_id' => $payment->voucher_no,
        ]);

        Payment::where('id', $id)->update([
            'is_ledger_added' => 1,
            'is_approved' => 1,
            'updated_at' => now()
        ]);

        DB::commit();
        return redirect()->route('admin.accounting.list_expenses',$id)->with('message', 'Expense approved successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('message', $e->getMessage());
    }
}

    public function csv_export_expenses(Request $request)
    {
        return Excel::download(
            new ExpensesExport($request->search, $request->entry_date),
            'Depot-expense-' . date('d-m-Y') . '.xlsx'
        );
    }

    // public function csv_export_expenses(Request $request){

    //     $search = !empty($request->search)?$request->search:'';



    //     $entry_date = !empty($request->entry_date)?$request->entry_date:'';



    //     $data = Payment::where('voucher_no', 'LIKE', 'EXPENSE%');

    //     if(!empty($search)){



    //         $data = $data->where('voucher_no', 'LIKE','%'.$search.'%')->orWhere('narration','LIKE','%'.$search.'%')->orWhereHas('creator', function($cr) use($search){



    //             $cr->where('name', 'LIKE','%'.$search.'%');



    //         });

    //     }



    //     if(!empty($entry_date)){



    //         $data = $data->where('payment_date',$entry_date);





    //     }



    //     $data = $data->orderBy('payment_date','desc')->get();

    //     if(count($data)>0){

    //              $delimiter = ",";

    //              $fileName = "Depot-expense-".date('d-m-Y').".csv";

    //              // Create a file pointer

    //              $f = fopen('php://memory', 'w');

                 

    //              // Set Column Headers

    //              $header = array("Staff","Payment For","Voucher No","Payment Date", "Amount", "Payment Type", "Payment Mode", "Bank Name", "Cheque URT No", "Narration", "Created By", "Created at");

    //         fputcsv($f,$header,$delimiter);

            

    //         $count =1;

    //           foreach($data as $key => $row){

    //             $staff_data = DB::table('users')->where('id', $row->staff_id)->first();

    //             $created_by = DB::table('users')->where('id', $row->created_by)->first();

    //             $exportData = array(

    //                 $row->staff_id ? $staff_data->name : '',

    //                 $row->payment_for ? $row->payment_for : '',    

    //                 $row->voucher_no ? $row->voucher_no : '',      

    //                 $row->payment_date ? $row->payment_date : '',      

    //                 $row->amount ? $row->amount : '',  

    //                 $row->bank_cash ? $row->bank_cash : '',

    //                 $row->payment_mode ? $row->payment_mode : '', 

    //                 $row->bank_name ? $row->bank_name : '',  

    //                 $row->chq_utr_no ? $row->chq_utr_no : '',      

    //                 $row->narration ? $row->narration : '',             

    //                 $row->created_by ? $created_by->name : '',             

    //                 $row->created_at ? $row->created_at : '',             

    //             );

    //                 fputcsv($f,$exportData,$delimiter);

    //                 $count++;

    //                 }

    //                  fseek($f,0);

    //                 // Set headers to download file rather than displayed

    //                 header('Content-Type: text/csv');

    //                 header('Content-Disposition: attachment; filename="' . $fileName . '";');

                    

    //                 //output all remaining data on a file pointer

    //                 fpassthru($f);

    //     }else {

    //          Session::flash('message', 'Data not found'); 

    //         return redirect()->back();

    //     }

    // }







    public function edit_expense($id,Request $request)



    {



        # Edit Depot Expense...



        $payment = Payment::find($id);



        // dd($payment);



        return view('admin.accounting.edit_expense', compact('id','payment'));



    }







    public function update_expense(Request $request)



    {



        # Update Depot Expense...



        //dd($request->all());



        $params = $request->except('_token');



        // dd($params);







        # Update Payment Master



        Payment::where('id', $params['payment_id'])->update([



            'payment_date' => $params['payment_date'],



            'amount' => $params['amount'],



            'payment_mode' => $params['payment_mode'],



            'chq_utr_no' => $params['chq_utr_no'],



            'bank_name' => $params['bank_name'],



            'narration' => $params['narration'],



            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 



            'updated_by' => Auth::user()->id,



            'updated_at' => date('Y-m-d H:i:s')



        ]);



        # Update Ledger



        Ledger::where('payment_id', $params['payment_id'])->update([



            'transaction_amount' => $params['amount'],



            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 



            'entry_date' => $params['payment_date'],



            'updated_at' => date('Y-m-d H:i:s')



        ]);



        # Update Journal 



        Journal::where('payment_id', $params['payment_id'])->update([



            'transaction_amount' => $params['amount'],



            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 



            'entry_date' => $params['payment_date'],



            'updated_at' => date('Y-m-d H:i:s')



        ]);







        $successMsg = "Depot Expense Updated Successfully";



        Session::flash('message', $successMsg); 



        return redirect()->route('admin.accounting.list_expenses');



    }























    public function add_payment_receipt(Request $request,$paymentCollectionId=0)



    {



        $payment_collection = array();



        $store_id = !empty($request->store_id)?$request->store_id:'';



        $store_name = !empty($request->store_name)?$request->store_name:'';



        



        $payment_collection = PaymentCollection::select('payment_collections.*','stores.store_name','users.name AS staff_name',)->leftJoin('stores','stores.id','payment_collections.store_id')->leftJoin('users','users.id','payment_collections.user_id')->find($paymentCollectionId);







        $stores = Store::select('id','store_name','bussiness_name')->orderBy('store_name')->get();



        $users = User::whereIn('designation',[1])->orWhere('type',1)->where('status',1)->get();  /* partners and salesman */



        // $users = User::select('id','name','mobile')->where('type', 2)->where('status', 1)->orderBy('name','asc')->get();







        



        return view('admin.accounting.add_payment_receipt', compact('paymentCollectionId','payment_collection','stores','users','store_id','store_name'));        



        



    }







    public function save_payment_receipt(Request $request)

    {     
        $request->validate([

            // 'payment_collection_id' => 'required',



            'store_id' => 'required',



            'staff_id' => 'required',



            'payment_date' => 'required', 



            'payment_mode' => 'required', 



            'amount' => 'required'            



        ],[    



            'store_id.required' => "Please add store",        



            'payment_date.required' => "Please add date of payment",



            'payment_mode' => "Please mention mode of payment",



            'amount.required' => "Please add amount",



            "staff_id.required" => "Please choose collected by"



        ]);

        try {
            DB::beginTransaction();
            $store_id = $request->store_id;

            $staff_id = !empty($request->staff_id)?$request->staff_id:'';

            $admin_id = Auth::user()->id;        

            $user_type = 'store';

            // dd($request->all());

            if(empty($request->payment_collection_id)){
                // echo $request->store_id;

                $check_store_unpaid_invoices = DB::table('invoice')->where('store_id', $request->store_id)->where('is_paid', 0)->get()->toarray();
    
                /*if(empty($check_store_unpaid_invoices)){
                    return  redirect()->back()->withErrors(['amount'=> "No unpaid invoice found of the store" ])->withInput();
                }*/

                $check_outstanding_amount = DB::table('invoice')->where('store_id',$request->store_id)->where('is_paid',0)->sum('required_payment_amount');

                $check_not_receipt_payment_amount = DB::table('payment_collections')->where('store_id',$request->store_id)->where('is_ledger_added',0)->first();

                /*if($request->amount > $check_outstanding_amount){

                    return  redirect()->back()->withErrors(['amount' => 'Please decrease your amount value. Unpaid outstanding amount is '.$check_outstanding_amount ])->withInput();

                }*/
            }



            ### Check Store City In ###

            $store = Store::find($store_id);

            $city_id = $store->city_id;

            // dd($store);

            // dd($store->city);

            if(!empty($city_id)){
                if(!in_array($staff_id,[1,2])){
                    // $checkUserCity = UserCity::where('user_id',$staff_id)->where('city_id',$city_id)->first();
                    $checkUserByCity = UserCity::where('city_id',$city_id)->first();

                    $city_name = getSingleAttributeTable('cities',$city_id,'name');

                    if(empty($checkUserByCity)){

                        return  redirect()->back()->withErrors(['staff_id'=> "There are currently no salesmen assigned to the city: $city_name."])->withInput();
                    }
                }
            }

            $paymentData = array(            



                'payment_for' => 'credit',



                'voucher_no' => $request->voucher_no,



                'payment_date' => $request->payment_date,



                'payment_mode' => $request->payment_mode,



                'payment_in' => ($request->payment_mode != 'cash') ? 'bank' : 'cash' ,



                'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank', 



                'amount' => $request->amount,



                'chq_utr_no' => !empty($request->chq_utr_no)?$request->chq_utr_no:'',



                'bank_name' => !empty($request->bank_name)?$request->bank_name:'',



                'created_by' => Auth::user()->id



            );



            



            if(!empty($staff_id)){



                $paymentStaff = array('staff_id' => $staff_id);



                $paymentData = array_merge($paymentData,$paymentStaff);



            } 



            if (!empty($store_id)){



                $paymentStore = array('store_id' => $store_id);



                $paymentData = array_merge($paymentData,$paymentStore);



            } 



            if (!empty($admin_id)){



                $paymentAdmin = array('admin_id' => $admin_id);



                $paymentData = array_merge($paymentData,$paymentAdmin);



            }



        



            $payment_id = Payment::insertGetId($paymentData);



            



            $is_credit = 1;        



            $is_debit = 0;



            



            $ledgerData = array(



                'user_type' => $user_type,



                'transaction_id' => $request->voucher_no,



                'transaction_amount' => $request->amount,



                'payment_id' => $payment_id,



                'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank',



                'is_credit' => $is_credit,



                'is_debit' => $is_debit,



                'entry_date' => $request->payment_date,



                'purpose' => 'payment_receipt',



                'purpose_description' => 'store payment'



            );



            if(!empty($staff_id)){



                $ledgerStaff = array('staff_id' => $staff_id);



                $ledgerData = array_merge($ledgerData,$ledgerStaff);



            } 



            if (!empty($store_id)){



                $ledgerStore = array('store_id' => $store_id);



                $ledgerData = array_merge($ledgerData,$ledgerStore);



            } 



            if (!empty($admin_id)){



                $ledgerAdmin = array('admin_id' => $admin_id);



                $ledgerData = array_merge($ledgerData,$ledgerAdmin);



            } 



                            



            Ledger::insert($ledgerData);







            /* Entry in journal */







            Journal::insert([



                'transaction_amount' => $request->amount,



                'is_credit' => $is_credit,



                'is_debit' => $is_debit,



                'entry_date' => $request->payment_date,



                'payment_id' => $payment_id,



                'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank',



                'purpose' => 'payment_receipt',



                'purpose_description' => 'store payment',



                'purpose_id' => $request->voucher_no



            ]);







            /* Payment Collection Entry */



            # This is for direct web entry



            if(empty($request->payment_collection_id)){



                $arrPaymentCollection = array(



                    'store_id' => $store_id,



                    'user_id' => $staff_id,



                    'admin_id' => $admin_id,



                    'payment_id' => $payment_id,



                    'collection_amount' => $request->amount,



                    'bank_name' => !empty($request->bank_name)?$request->bank_name:'',



                    'cheque_number' => !empty($request->chq_utr_no)?$request->chq_utr_no:'',



                    'cheque_date' => $request->payment_date,



                    'payment_type' => $request->payment_mode,



                    'vouchar_no' => $request->voucher_no,



                    'is_ledger_added' => 1,



                    'is_approve' => 1,



                    'created_from' => 'web',



                    'created_at' => date('Y-m-d H:i:s'),



                    'updated_at' => date('Y-m-d H:i:s')



                );



                $payment_collection_id = PaymentCollection::insertGetId($arrPaymentCollection);



                $this->invoicePayments($request->voucher_no,$request->payment_date,$request->amount,$store_id,$payment_collection_id,$staff_id);

            }

            /* ++++++++++++++++++++++++ */
            if(!empty($request->payment_collection_id)){



                # From App End
                DB::table('payment_collections')->where('id',$request->payment_collection_id)->update([
                    'payment_id' => $payment_id,



                    'is_ledger_added' => 1,



                    'vouchar_no' => $request->voucher_no,



                    'updated_at' => date('Y-m-d H:i:s')



                ]);







                $this->invoicePayments($request->voucher_no,$request->payment_date,$request->amount,$store_id,$request->payment_collection_id,$staff_id);



            }        







            



            // if(!empty($staff_id)){



            //     if(!in_array($staff_id, [1,2])){



            //         $this->staffCommission($request->voucher_no);



            //     }



                



            // }        

            Session::flash('message', 'Payment receipt added successfully');
    
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('message', $e->getMessage());
            \Log::error('Error in invoicePayments: ' . $e->getMessage());
        }
        return redirect()->route('admin.paymentcollection.index');
    }







    







    public function listopeningbalance(Request $request)



    {



        # code...







        $data = DB::table('ledger AS l')->select('l.*','s.store_name','s.bussiness_name','s.contact','s.whatsapp','p.payment_mode','p.bank_cash','p.payment_for','p.payment_in','p.bank_name','p.narration')->leftJoin('stores AS s','s.id','l.store_id')->leftJoin('payment AS p','p.id','l.payment_id')->where('l.purpose','opening_balance')->where('l.user_type','store')->orderBy('l.id','desc')->paginate(20);



        return view('admin.accounting.list_openning_balance', compact('data'));



    }







    public function editopeningbalance(Request $request)

    {

        // dd($request->all());

        try {

            DB::beginTransaction();

            $request->validate([

                'transaction_amount' => 'required|numeric|min:0.01',

                'id' => 'required',

            ]);

        

             // Process the valid data if validation passes

            $ledger = Ledger::find($request->id);

            $ledger->transaction_amount = $request->transaction_amount;

            $ledger->save();



            $payment_id = $ledger->payment_id;

            $Payment = Payment::where('id', $payment_id)->first();



            if ($Payment) {

                $Payment->amount = $request->transaction_amount;

                $Payment->save();

            }

            $Journal = Journal::where('payment_id', $payment_id)->first();



            if ($Journal) {

                $Journal->transaction_amount = $request->transaction_amount;

                $Journal->save();

            }

            DB::commit(); 

            Session::flash('message', 'Opening balance updated successfully'); 

            return redirect()->route('admin.accounting.listopeningbalance');

        

        } catch (\Illuminate\Validation\ValidationException $e) {

            DB::rollBack(); // Rollback the transaction in case of validation error

            Session::flash('error', $e->validator->errors()->first('transaction_amount'));

            return redirect()->back(); // Redirect back with input data

        

        } catch (\Exception $e) {

            DB::rollBack(); // Rollback the transaction in case of any other errors

            Session::flash('error', 'An error occurred while processing your request.');

            return redirect()->back(); // Redirect back with input data

        }

    }



    public function deleteopeningbalance($id)

    {

        $ledger = DB::table('ledger')->find($id);



        $payment_id = $ledger->payment_id;



        Ledger::where('id',$id)->delete();  # delete ledger record



        Payment::where('id',$payment_id)->delete();  # delete payment record



        Journal::where('payment_id',$payment_id)->delete();  # delete journal record

        Session::flash('message', 'Opening balance deleted successfully'); 

        return redirect()->route('admin.accounting.listopeningbalance');

    }







    public function add_expense_partner_withdrawls(Request $request, $withdrawls_id=0)



    {



        # expense form for partner withdrawl amount



        



        $withdrawls = DB::table('withdrawls AS w')->select('w.*','u.name','u.email')->leftJoin('users AS u','u.id','w.admin_id')->where('w.id',$withdrawls_id)->first();



        



        if(!empty($withdrawls)){



            // dd($withdrawls);



            $expense_partner_withdrawl = DB::table('expense')->where('slug', 'partner-withdrawl')->first();



            $expense_id = $expense_partner_withdrawl->id;



            return view('admin.accounting.add_withdrawl', compact('withdrawls','expense_id'));



        } else {



            return redirect()->route('admin.revenue.withdrawls');



        }



        



    }







    public function edit_payment_receipt($voucher_no,$ledger_url,Request $request)



    {



        // die('This feature is under development');



        $payment_collection = PaymentCollection::where('vouchar_no',$voucher_no)->first();



        $invoice_payments = InvoicePayment::where('voucher_no',$voucher_no)->orderBy('id','desc')->get();



        return view('admin.accounting.edit_payment_receipt', compact('payment_collection','voucher_no','invoice_payments','ledger_url'));



    }







    public function update_payment_receipt(Request $request)



    {



        # Update Payment ...







        $params = $request->except('_token');



        // echo '<pre>'; print_r($params); 



        $store_id = $params['store_id'];



        $ledger_url = $params['ledger_url'];



        if($params['old_payment_amount'] != $params['amount']){



            # Update Payment Collection



            PaymentCollection::where('payment_id',$params['payment_id'])->update([



                'collection_amount' => $params['amount'],



                'updated_at' => date('Y-m-d H:i:s')



            ]);



            



            $paymentIds = $collection_data = array();



            $old_payment_collections = PaymentCollection::where('store_id',$store_id)->where('is_approve', 1)->orderBy('cheque_date','asc')->get();



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



            



            $payment_collection_json = json_encode($collection_data);



            // dd($payment_collection_json);



            $paymentUpdate = array(



                'store_id' => $store_id,



                'edited_by' => Auth::user()->id,



                'payment_id' => $params['payment_id'],



                'voucher_no' => $params['voucher_no'],



                'amount' => $params['amount'],



                'old_payment_amount' => $params['old_payment_amount'],



                'payment_collection_json' => $payment_collection_json,



                'created_at' => date('Y-m-d H:i:s'),



                'updated_at' => date('Y-m-d H:i:s') 



            );



            // dd($paymentUpdate);



            PaymentUpdate::insert($paymentUpdate);



            // die;



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



            // echo '<pre>invoiceIds:- '; print_r($invoiceIds);



            



            







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







            # Update Ledger



            Ledger::where('payment_id',$params['payment_id'])->update([



                'transaction_amount' => $params['amount'],



                'updated_at' => date('Y-m-d H:i:s')



            ]);



            # Update Journal



            Journal::where('payment_id',$params['payment_id'])->update([



                'transaction_amount' => $params['amount'],



                'updated_at' => date('Y-m-d H:i:s')



            ]);



            # Update Payment



            Payment::where('id',$params['payment_id'])->update([



                'amount' => $params['amount'],



                'updated_at' => date('Y-m-d H:i:s')



            ]);







            // echo '<pre>commisionIds:- '; print_r($commisionIds); 



            $this->resetInvoicePayments($store_id,$collection_data);



            // die;



            



        }



                 



        Session::flash('message', 'Payment receipt updated successfully'); 



        // return redirect()->route('admin.paymentcollection.index'); 



        return redirect('/admin/report/choose-ledger-user?'.$ledger_url);



        



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







    private function invoicePayments($voucher_no,$payment_date,$payment_amount,$store_id,$payment_collection_id,$staff_id)

    {

        // die('voucher_no : '.$voucher_no.'<br/>payment_amount : '.$payment_amount.'<br/>store_id : '.$store_id.' ');

        // DB::enableQueryLog();
        try {
            DB::beginTransaction();
            $check_invoice_payments = DB::table('invoice_payments')->where('voucher_no','=',$voucher_no)->get()->toarray();

            // dd($check_invoice_payments);

            if(empty($check_invoice_payments)){



                



                // die('No invoice payments found');



                // dd('Hi');



                $amount_after_settlement = $payment_amount;



                /* Check store unpaid invoices */



                $invoice = Invoice::where('store_id',$store_id)->where('is_paid',0)->orderBy('id','asc')->get();



                



                // dd($invoice);







                $sum_inv_amount = 0;



                foreach($invoice as $inv){







                    $invoice_date = date('Y-m-d', strtotime($inv->created_at));



                    $invoiceOld = date_diff(



                        date_create($invoice_date),  



                        date_create($payment_date)



                    )->format('%a');



                    $year_val = date('Y', strtotime($payment_date));



                    $month_val = date('m', strtotime($payment_date));







                    $payment_collection = PaymentCollection::find($payment_collection_id);



                    $payment_id = $payment_collection->payment_id;                



                    $store = Store::find($store_id);



                    $city_id = $store->city_id;



                    $amount = $inv->required_payment_amount;



                    $sum_inv_amount += $amount;



                     //echo 'amount:- '.$amount.'<br/>';



                    // echo 'payment_amount:- '.$payment_amount.'<br/>';
                     //echo 'invoice_old:- '.$invoiceOld.'<br/>';

                     //die();

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

                            'voucher_no' => $voucher_no,

                            'invoice_amount' => $inv->net_price,

                            'vouchar_amount' => $payment_amount,

                            'paid_amount' => $amount,

                            'rest_amount' => '',

                            'created_at' => date('Y-m-d H:i:s'),

                            'updated_at' => date('Y-m-d H:i:s')

                        ]);



                        


                        if(!empty($staff_id)){

                            if(!in_array($staff_id, [1,2])){

                                if($invoiceOld <= 60){
                                    $checkUserByCity = UserCity::where('city_id',$city_id)->first();

                                    $salesman_id = $checkUserByCity?$checkUserByCity->user_id:$staff_id;
                                    $eligibleArr = array(

                                        'user_id' => $salesman_id,

                                        'store_id' => $store_id,

                                        'invoice_id' => $inv->id,

                                        'payment_id' => $payment_id,

                                        'collect_within_days' => $invoiceOld,

                                        'invoice_paid_amount' => $amount,

                                        'city_id' => $city_id,

                                        'year_val' => $year_val,

                                        'month_val' => $month_val,

                                        'created_at' => date('Y-m-d H:i:s'),

                                        'updated_at' => date('Y-m-d H:i:s')

                                    );

                                    DB::table('staff_collection_commission_eligibility')->insert($eligibleArr);

                                }

                                ### For Now Invoice Staff Commission Is Off , Generating salesman payment commission through report section

                                #####

                                // $this->staffCommission($voucher_no);

                            }                        

                        } 


                        $amount_after_settlement = 0;

                    } else{
                        // die('Not Full Covered');
                        if($amount_after_settlement>$amount && $amount_after_settlement>0){
                            $amount_after_settlement=$amount_after_settlement-$amount;
                            Invoice::where('id',$inv->id)->update([
                                'required_payment_amount'=>'',
                                'payment_status' => 2,
                                'is_paid'=>1
                            ]);

                            InvoicePayment::insert([

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

                            if(!empty($staff_id)){

                                if(!in_array($staff_id, [1,2])){

                                    if($invoiceOld <= 60){

                                        $checkUserByCity = UserCity::where('city_id',$city_id)->first();

                                        $salesman_id = $checkUserByCity?$checkUserByCity->user_id:$staff_id;
                                        $eligibleArr = array(
                                            'user_id' => $salesman_id,

                                            'store_id' => $store_id,

                                            'invoice_id' => $inv->id,

                                            'payment_id' => $payment_id,

                                            'collect_within_days' => $invoiceOld,

                                            'invoice_paid_amount' => $amount,

                                            'city_id' => $city_id,

                                            'year_val' => $year_val,

                                            'month_val' => $month_val,

                                            'created_at' => date('Y-m-d H:i:s'),

                                            'updated_at' => date('Y-m-d H:i:s')

                                        );

                                        DB::table('staff_collection_commission_eligibility')->insert($eligibleArr);

                                    }

                                }                        

                            }

                        }else if($amount_after_settlement<$amount && $amount_after_settlement>0){

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

                                'voucher_no' => $voucher_no,

                                'invoice_amount' => $inv->net_price,

                                'vouchar_amount' => $payment_amount,

                                'paid_amount' => $amount_after_settlement,

                                'rest_amount' => $rest_payment_amount,

                                'created_at' => date('Y-m-d H:i:s'),



                                'updated_at' => date('Y-m-d H:i:s')



                            ]);







                            if(!empty($staff_id)){



                                if(!in_array($staff_id, [1,2])){



                                    



                                    if($invoiceOld <= 60){

                                        $checkUserByCity = UserCity::where('city_id',$city_id)->first();

                                        $salesman_id = $checkUserByCity?$checkUserByCity->user_id:$staff_id;

                                        $eligibleArr = array(



                                            'user_id' => $salesman_id,



                                            'store_id' => $store_id,



                                            'invoice_id' => $inv->id,



                                            'payment_id' => $payment_id,



                                            'collect_within_days' => $invoiceOld,



                                            'invoice_paid_amount' => $amount_after_settlement,



                                            'city_id' => $city_id,



                                            'year_val' => $year_val,



                                            'month_val' => $month_val,



                                            'created_at' => date('Y-m-d H:i:s'),



                                            'updated_at' => date('Y-m-d H:i:s')



                                        );



                                        DB::table('staff_collection_commission_eligibility')->insert($eligibleArr);



                                    }



                                    



                                }                        



                            }







                            



        



                            $amount_after_settlement = 0;



                                                



                        }else if($amount_after_settlement==0){



                            



                        }



                        if(!empty($staff_id)){



                            if(!in_array($staff_id, [1,2])){







                                ### For Now Invoice Staff Commission Is Off , Generating salesman payment commission through report section



                                #####



                                // $this->staffCommission($voucher_no);



                            }                        



                        }



                    }

                }

            }else{

            }
        DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in invoicePayments: ' . $e->getMessage());
        }
    }


    private function staffCommission($voucher_no)



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



    



                $payment_collector_commision_id = StaffCommision::insertGetId([



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



    



                InvoicePayment::where('id',$inv->id)->update([



                    'is_commisionable' => 1,



                    'updated_at' => date('Y-m-d H:i:s')



                ]);



    



                /* +++++++++++++++++++++++++++++++++++++++++++++++++++++ */



    



            }



        }



        



    }







    public function add_staff_expense(Request $request)



    {



        $expense_types = Expense::where('for_staff', 1)->where('for_credit', 1)->where('status', 1)->orderBy('title')->get();







        $staff = User::whereNotNull('designation')->where('status', 1)->orderBy('name')->get();







        return view('admin.accounting.add_staff_expense', compact('expense_types','staff'));



    }







    public function save_staff_expense(Request $request)



    {



        $request->validate([



            'staff_id' => 'required',



            'amount' => 'required',



            'payment_date' => 'required',



            'payment_mode' => 'required',



            'chq_utr_no' => 'required_unless:payment_mode,cash',



            'bank_name' => 'required_unless:payment_mode,cash',



            'expense_id' => 'required'



        ],[



            'staff_id.required' => 'Please add staff',



            'amount.required' => 'Please add amount',



            'payment_date.required' => 'Please add date',



            'payment_mode.required' => 'Please add mode of payment',



            'chq_utr_no.required_unless' => 'Please add Cheque No or UTR No',



            'bank_name.required_unless' => 'Please add bank name',



            'expense_id.required' => 'Please add expense'



        ]);



        $params = $request->except('_token');



        // dd($params);



        $paymentData = array(



            'staff_id' => $params['staff_id'],



            'voucher_no' => $params['voucher_no'],



            'expense_id' => $params['expense_id'],



            'payment_for' => 'credit',



            'payment_date' => $params['payment_date'],



            'payment_mode' => $params['payment_mode'],



            'payment_in' => ($params['payment_mode'] != 'cash') ? 'bank' : 'cash' ,



            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 



            'amount' => $params['amount'],



            'bank_name' => $params['bank_name'],



            'chq_utr_no' => $params['chq_utr_no'],



            'narration' => $params['narration'],



            'created_by' => Auth::user()->id,



            'created_at' => date('Y-m-d H:i:s'),



            'updated_at' => date('Y-m-d H:i:s')



        ); 



        $payment_id = Payment::insertGetId($paymentData);



        $expense_name = getSingleAttributeTable('expense',$params['expense_id'],'title');



        $purpose_description = "expense for staff. ".$expense_name;



        $ledgerData = array(



            'user_type' => 'staff',



            'staff_id' => $params['staff_id'],



            'transaction_id' => $params['voucher_no'],



            'transaction_amount' => $params['amount'],



            'payment_id' => $payment_id,



            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank',



            'is_credit' => 1,



            'entry_date' => $params['payment_date'],



            'purpose' => 'staff_expense',



            'purpose_description' => $purpose_description,



            'created_at' => date('Y-m-d H:i:s'),



            'updated_at' => date('Y-m-d H:i:s')



        );



        Ledger::insert($ledgerData);



        



        $successMsg = "Staff Expense added successfully, Amount will be reflected on the ledger.";



        Session::flash('message', $successMsg); 



        return redirect()->route('admin.accounting.add_staff_expense');



    }







    public function list_bad_debt(Request $request)



    {



        $data = StoreBadDebt::orderBy('id', 'desc')->paginate(20);



        return view('admin.accounting.list_bad_debt', compact('data'));



    }







    public function add_bad_debt(Request $request)



    {



        return view('admin.accounting.add_bad_debt');



    }







    public function save_bad_debt(Request $request)



    {        



        $request->validate([



            'store_id' => 'required',



            'payment_date' => 'required', 



            'payment_mode' => 'required', 



            'amount' => 'required|not_in:0'            



        ],[    



            'store_id.required' => "Please add store",        



            'payment_date.required' => "Please add date of payment",



            'payment_mode' => "Please mention mode of payment",



            'amount.required' => "Please add amount"



        ]);







        // $params = $request->except('_token');



        // dd($params);







        $store_id = $request->store_id;     



        $user_type = 'store';







        $store_bad_debt_id = StoreBadDebt::insertGetId([



            'store_id' => $store_id,



            'amount' => $request->amount,



            'created_at' => date('Y-m-d H:i:s')



        ]);                



        



        $paymentData = array(            



            'payment_for' => 'credit',



            'store_id' => $store_id,



            'voucher_no' => $request->voucher_no,



            'payment_date' => $request->payment_date,



            'payment_mode' => $request->payment_mode,



            'payment_in' => ($request->payment_mode != 'cash') ? 'bank' : 'cash' ,



            'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank', 



            'amount' => $request->amount,



            'chq_utr_no' => !empty($request->chq_utr_no)?$request->chq_utr_no:'',



            'bank_name' => !empty($request->bank_name)?$request->bank_name:'',



            'created_by' => Auth::user()->id



        );        



       



        $payment_id = Payment::insertGetId($paymentData);



        



        $ledgerData = array(



            'user_type' => $user_type,



            'transaction_id' => $request->voucher_no,



            'transaction_amount' => $request->amount,



            'payment_id' => $payment_id,



            'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank',



            'is_credit' => 1,



            'entry_date' => $request->payment_date,



            'store_bad_debt_id' => $store_bad_debt_id,



            'store_id' => $store_id,



            'purpose' => 'bad_debt',



            'purpose_description' => 'store bad debt'



        );



               



        Ledger::insert($ledgerData);







        /* Entry in journal */







        Journal::insert([



            'transaction_amount' => $request->amount,



            'is_credit' => 1,



            'entry_date' => $request->payment_date,



            'payment_id' => $payment_id,



            'bank_cash' => ($request->payment_mode == 'cash') ? 'cash' : 'bank',



            'purpose' => 'bad_debt',



            'purpose_description' => 'store bad debt',



            'purpose_id' => $request->voucher_no



        ]);







        Session::flash('message', 'Debt added successfully for store');



        return redirect()->route('admin.accounting.add_bad_debt');

    }
	
	public function get_staff(Request $request) {

		 $query = $request->query('query');

		$staffSuggestions = User::where(function ($q) {
				$q->whereIn('designation', [1])
				  ->orWhere('type', 1);
			})
			->where('status', 1)
			->where('name', 'like', "%{$query}%")
			->pluck('name');


		return response()->json($staffSuggestions);
	}

	
	
	public function cash_book_module(Request $request){
		$user = Auth::guard('web')->user();
		$notSuperAdmin = $user->name !== 'Super Admin';
		$startDate = $request->input('start_date', Carbon::now()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $staffName = $request->input('staff_name');
		
		// Find staff ID by name if staff name is provided
        $selectedStaffId = null;
        if ($staffName) {
            $staff = User::where('name', $staffName)->first();
            if ($staff) {
                $selectedStaffId = $staff->id;
            }
        }
		
		$firstCollectionDate = PaymentCollection::where('is_approve', 1)
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('created_at')
            ->value('created_at');
		
		$firstExpenseDate = Journal::where('is_debit', 1)
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->whereHas('payment', function ($q) use ($user) {
                    $q->where('staff_id', $user->id);
                });
            })
            ->orderBy('created_at')
            ->value('created_at');
		
		// Opening Balance (Past Collections)
        $pastCollections = PaymentCollection::where('is_approve', 1)
            ->whereDate('created_at', '<', $startDate)
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->where('user_id', $user->id); // Staff's collections
            })
            ->sum('collection_amount');

        // Opening Balance (Past Expenses)
        $pastExpenses = Journal::where('is_debit', 1)
            ->whereDate('created_at', '<', $startDate)
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->whereHas('payment', function ($q) use ($user) {
                    $q->whereNotNull('staff_id')->where('staff_id', $user->id); // Store's expenses
                });
            })
            ->sum('transaction_amount');
		
		

        $openingBalance = $pastCollections - $pastExpenses;
		
		// Total Collections
        $collectionQuery = $this->getCollectionQuery($user, $notSuperAdmin, $selectedStaffId, $startDate, $endDate);
        $totalCollections = $collectionQuery->sum('collection_amount');
		
		//Cash Collection
		$totalcashCollections = $this->getCollectionQuery($user,$notSuperAdmin, $selectedStaffId, $startDate, $endDate)
            ->where('payment_type', 'cash')
            ->sum('collection_amount');
		
		// NEFT Collection
        $totalneftCollections = $this->getCollectionQuery($user,$notSuperAdmin, $selectedStaffId, $startDate, $endDate)
            ->where('payment_type', 'neft')
            ->sum('collection_amount');
		
		// Cheque Collection
        $totalchequeCollections = $this->getCollectionQuery($user,$notSuperAdmin, $selectedStaffId, $startDate, $endDate)
            ->where('payment_type', 'cheque')
            ->sum('collection_amount');
		
		// Total Expenses
        $expenseQuery = Journal::where('is_debit', 1)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->whereHas('payment', function ($q) use ($user) {
                    $q->whereNotNull('staff_id')->where('staff_id', $user->id);
                });
            });
           
        
        $totalExpenses = $expenseQuery->sum('transaction_amount');
		
		$totalWallet = $openingBalance + ($totalCollections - $totalExpenses);
		
		// Get payment collections for table
        $paymentCollections = $this->getCollectionQuery($user,$notSuperAdmin, $selectedStaffId, $startDate, $endDate)
            ->where('collection_amount', '>', 0)
            ->with(['stores', 'users'])
            ->orderByDesc('created_at')
            ->get();
		
		 // Get expenses for table
        $validPaymentIds = Journal::whereNotNull('payment_id')->pluck('payment_id');
        $paymentExpenses = Payment::where('payment_for', 'debit')
            ->whereIn('id', $validPaymentIds)
            ->whereBetween('created_at', [
				Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
			])
            ->when($notSuperAdmin, function ($query) use ($user) {
                $query->where('staff_id', $user->id);
            })
            ->when($selectedStaffId, function($query) use ($selectedStaffId) {
                $query->where('staff_id', $selectedStaffId);
            })
            ->with(['staff', 'store', 'supplier', 'creator', 'partner','updater'])
            ->orderByDesc('created_at')
            ->get();
		
		// ------------------------------------
		// Total Sales (Final Amount)
		// ------------------------------------
		$orderSalesQuery = Order::query()
			->whereBetween('created_at', [
				Carbon::parse($startDate)->startOfDay(),
				Carbon::parse($endDate)->endOfDay()
			])
			->whereNotNull('final_amount');
		
		// If not super admin → only own orders
		$orderSalesQuery->when($notSuperAdmin, function ($q) use ($user) {
			$q->where('user_id', $user->id);
		});
		
		
		// If staff filter selected
		$orderSalesQuery->when($selectedStaffId, function ($q) use ($selectedStaffId) {
			$q->where('user_id', $selectedStaffId);
		});
		
		$totalOrderSales = $orderSalesQuery->sum('final_amount');
		
		return 		view('admin.accounting.cash_book_module',compact('startDate','endDate','staffName','totalCollections','totalExpenses','totalWallet','totalcashCollections','totalneftCollections','totalchequeCollections','paymentCollections','paymentExpenses','totalOrderSales'));
	}


		private function getCollectionQuery(
		$user,
		$notSuperAdmin,
		$selectedStaffId,
		$startDate,
		$endDate
	) {
		return PaymentCollection::where('is_approve', 1)
			->when($notSuperAdmin, function ($query) use ($user) {
				$query->where('user_id', $user->id);
			})
			->when($selectedStaffId, function ($query) use ($selectedStaffId) {
            $query->where('user_id', $selectedStaffId);
        })
			
			->whereBetween('created_at', [
				\Carbon\Carbon::parse($startDate)->startOfDay(),
				\Carbon\Carbon::parse($endDate)->endOfDay()
			]);
	}
	
	public function day_cash_entry(){
		$staffs = User::where(function ($q) {
            $q->whereIn('designation', [1])
              ->orWhere('type', 1);
        })
        ->where('status', 1)
        ->orderBy('name')
        ->get();
		return view('admin.accounting.day_cash_entry',compact('staffs'));
	}




}
