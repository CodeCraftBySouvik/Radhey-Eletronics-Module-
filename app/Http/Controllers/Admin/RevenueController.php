<?php



namespace App\Http\Controllers\Admin;



use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Illuminate\Http\Response;

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Str;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Session;

use App\Models\Withdraw;

use App\Models\Invoice;

use App\Models\Ledger;

use App\Models\Journal;

use App\Models\Payment;

use Carbon\Carbon;



class RevenueController extends Controller

{

    //

    private $investment_percentage;

    private $withdraw_percentage;

    public function __construct(Request $request)

    {

        # app settings...

        $settings = DB::table('settings')->find(1);

        $this->investment_percentage =$settings? $settings->investment_percentage: '0';

        $this->withdraw_percentage = $settings? $settings->withdraw_percentage : '0';

    }



     public function details(Request $request)

    {

        // dd($request->all());

        

        $designation = Auth::user()->designation;

        $auth_type = Auth::user()->type;

        if($auth_type == 2){

            $userAccesses = userAccesses($designation,11);

            if(!$userAccesses){

                abort(401);

            }

        }

        // Debugging: Uncomment the following line if you need to see the structure

        // dd($purchase_order_data);

        // dd($purchase_order_ids);

        // $purchase_order_products =DB::table('purchase_order_products')->select('product_id' as 'product', )->where('purchase_order_id', $stocks_products->purchase_order_id)->get();

        # total sales

        $first_sale = DB::table('invoice_products')->orderBy('id', 'ASC')->first();
	
        $start_date = isset($request->from_date)?$request->from_date.' 00:00:00':date('Y-m-01 h:i:s');
		 
		$endOfDay = date('Y-m-d') . ' 23:59:59';
		 $to_date = isset($request->to_date)?$request->to_date.' 23:59:59':$endOfDay;

        $goods_purchased_amount = DB::table('invoice_products')->whereBetween('created_at', [$start_date, $to_date])->sum('count_price');

        $total_sales = DB::table('invoice_products')->whereBetween('created_at', [$start_date, $to_date])->sum('total_price');

       

        # total expense

       // Start the query builder
		 $query = DB::table('journal')->where('is_debit', 1);

		 // Conditionally add the whereBetween clause if $start_date is provided
		 if ($start_date) {
			 $query->whereBetween('created_at', [$start_date, $to_date]);
		 }

		 // Get the sum of transaction_amount
		 $total_expense = $query->sum('transaction_amount');
        # net profit

        $total_staff_credit = DB::table('ledger')->where('user_type','staff')->where('is_credit', 1)->sum('transaction_amount');

        $total_staff_debit = DB::table('ledger')->where('user_type','staff')->where('is_debit', 1)->sum('transaction_amount');

        $total_staff_outstanding = ($total_staff_credit - $total_staff_debit); 



        



        #revenue collected :- (total payment collection + store OB + store service slip)

        $total_payment_collection = DB::table('journal')->where('purpose', 'payment_receipt')->whereBetween('created_at', [$start_date, $to_date])->sum('transaction_amount');

        $total_store_opening_balance = Ledger::whereNotNull('store_id')->where('user_type','store')->where('purpose', 'opening_balance')->whereBetween('created_at', [$start_date, $to_date])->sum('transaction_amount');        

        $total_store_service_slip = Journal::where('purpose', 'service_slip')->whereBetween('created_at', [$start_date, $to_date])->sum('transaction_amount');

        $total_revenue_collected = ($total_payment_collection + $total_store_opening_balance + $total_store_service_slip);

        $net_profit = ($total_revenue_collected - $total_expense);
        // $net_profit = (($total_revenue_collected+$total_staff_outstanding) - $total_expense);



        # net profit margin

        # Net Profit margin = Net Profit ⁄ Total revenue x 100

        $net_profit_margin = $profit_in_hand = 0;

        if(!empty($total_revenue_collected)){

            $net_profit_margin = (($net_profit / $total_revenue_collected ) * 100 );
            $net_profit_margin = number_format((float)$net_profit_margin, 2, '.', '');

            $profit_in_hand = getPercentageVal($total_revenue_collected,$net_profit_margin);

            $profit_in_hand = number_format((float)$profit_in_hand, 2, '.', '');

        }

        

       

        # reserved amount

        $withdraw_percentage = $this->withdraw_percentage; 

        $investment_percentage = $this->investment_percentage; 

        $reserved_amount = getPercentageVal($profit_in_hand,$investment_percentage);

        $reserved_amount = number_format((float)$reserved_amount, 2, '.', '');

        # withdrawable amount

        $withdrawable_amount = getPercentageVal($profit_in_hand,$withdraw_percentage);

        $withdrawable_amount = number_format((float)$withdrawable_amount, 2, '.', '');

        # partner net required amount / summation of withdraw required amount 

        $partner_required_amount = DB::table('withdrawls')->where('admin_id', Auth::user()->id)->where('is_disbursed')->sum('required_amount');

        # withdrawable amount each

        $withdrawable_amount_each = getPercentageVal($withdrawable_amount,50);

        $withdrawable_amount_each = number_format((float)$withdrawable_amount_each, 2, '.', '');

        # net partner withdrawable amount

        $withdrawable_amount_each = ($partner_required_amount + $withdrawable_amount_each);

        // dd($withdrawable_amount_each);



        /* Gross Profit = (Total Goods Sold - Total Goods Purchased) + Service Slips */

        // $goods_purchased_amount = DB::table('stock')->whereNotNull('purchase_order_id')->sum('total_price');

        $total_service_slips = DB::table('service_slip')->whereBetween('created_at', [$start_date, $to_date])->sum('amount');



        

        $gross_profit = (($total_sales-$goods_purchased_amount)+$total_service_slips);

        // dd($goods_purchased_amount);



         /* Gross Profit % = (Gross profit amount / Sold Goods Purchased amount) * 100 */

        $gross_profit_percentage = 0;

         if($goods_purchased_amount>0){

             $gross_profit_percentage = ((($total_sales-$goods_purchased_amount)/$total_sales)*100);
             $gross_profit_percentage = number_format((float)$gross_profit_percentage, 2, '.', '');

         }



        $month_val = !empty($request->month_val)?$request->month_val:date('m');

        $year_val = !empty($request->year_val)?$request->year_val:date('Y');



        // $from_date = date('Y-m-01', strtotime($year_val.'-'.$month_val));

        // $to_date = date('Y-m-t', strtotime($year_val.'-'.$month_val));



        // $from_date = !empty($request->from_date) ? $request->from_date : date('Y-m-01', strtotime($year_val.'-01'));

        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-01', strtotime(date('Y-m-d')));

        // echo $from_date; die;

        $to_date = !empty($request->to_date) ? $request->to_date : date('Y-m-d');

        // $to_date = !empty($request->to_date) ? $request->to_date : date('Y-m-t', strtotime($year_val.'-'.$month_val));

        $bank_cash = !empty($request->bank_cash)?$request->bank_cash:'';



        $data = array();



        $getDaysFromDateRange = getDaysFromDateRange($from_date,$to_date);

        

        // if($getDaysFromDateRange > 365) {

        //     $err_msg = "Please choose dates within 365 days";

        //     return  redirect()->back()->withErrors(['from_date'=> $err_msg])->withInput();

        // }



        $data = DB::table('journal AS l')->select('l.*','p.voucher_no','p.payment_in','p.amount AS payment_amount','p.payment_mode','p.chq_utr_no','p.narration');



        $journal_started = DB::table('journal')->min('entry_date');

        

        if(!empty($journal_started)  ){

            // $from_date = ($request->from_date < $journal_started) ? $journal_started : $request->from_date;

            $min_from_date = $journal_started;

        } else{

            $min_from_date = $from_date;

        }

        

        if(!empty($from_date) && !empty($to_date)){

            $data = $data->whereRaw("( l.entry_date BETWEEN '".$from_date."' AND '".$to_date."')");

        } 

        

        if(!empty($bank_cash)){

            $data = $data->where('l.bank_cash', $bank_cash);

        }

        $data = $data->leftJoin('payment AS p','p.id','l.payment_id');

        $data = $data->orderBy('l.entry_date','asc');

        $data = $data->orderBy('l.updated_at','asc');  

        $data = $data->get();



        // dd($data);

        

        return view('admin.journal.index', compact('data','from_date','min_from_date','month_val','bank_cash','year_val','to_date','total_sales','total_staff_outstanding','total_expense','net_profit','net_profit_margin','total_revenue_collected','profit_in_hand','reserved_amount','withdrawable_amount','withdrawable_amount_each','gross_profit', 'gross_profit_percentage'));

        

    }



    public function withdraw_form(Request $request)

    {        

        $designation = Auth::user()->designation;

        $auth_type = Auth::user()->type;

        if($auth_type == 2){

            $userAccesses = userAccesses($designation,11);

            if(!$userAccesses){

                abort(401);

            }

        }



        # check existing not pending request

        $checkExistPending = DB::table('withdrawls')->where('admin_id', Auth::user()->id)->where('is_disbursed', 0)->first();



        // dd($checkExistPending);



        if(!empty($checkExistPending)){

            Session::flash('message', 'You have already pending withdrawl !! Please ask to accountant to disburse'); 

            Session::flash('alert-class', 'alert-info');

            return redirect()->route('admin.revenue.index');

        }



        # total sales

        $total_sales = DB::table('invoice')->sum('net_price');

        # total expense

        $total_expense = DB::table('journal')->where('is_debit',1)->sum('transaction_amount');

        # net profit

        $total_staff_credit = DB::table('ledger')->where('user_type','staff')->where('is_credit', 1)->sum('transaction_amount');

        $total_staff_debit = DB::table('ledger')->where('user_type','staff')->where('is_debit', 1)->sum('transaction_amount');

        $total_staff_outstanding = ($total_staff_credit - $total_staff_debit); 



        



        #revenue collected :- (total payment collection + store OB + store service slip)

        $total_payment_collection = DB::table('journal')->where('purpose', 'payment_receipt')->sum('transaction_amount');

        $total_store_opening_balance = Ledger::whereNotNull('store_id')->where('user_type','store')->where('purpose', 'opening_balance')->sum('transaction_amount');        

        $total_store_service_slip = Journal::where('purpose', 'service_slip')->sum('transaction_amount');

        $total_revenue_collected = ($total_payment_collection + $total_store_opening_balance + $total_store_service_slip);



        $net_profit = ($total_revenue_collected - $total_expense);

        // $net_profit = (($total_revenue_collected+$total_staff_outstanding) - $total_expense);



        # net profit margin

        # Net Profit margin = Net Profit ⁄ Total revenue x 100

        $net_profit_margin = $profit_in_hand = 0;

        if(!empty($total_revenue_collected)){

            $net_profit_margin = (($net_profit / $total_revenue_collected ) * 100 );

            $net_profit_margin = number_format((float)$net_profit_margin, 2, '.', '');

            $profit_in_hand = getPercentageVal($total_revenue_collected,$net_profit_margin);

            $profit_in_hand = number_format((float)$profit_in_hand, 2, '.', '');

        }

        

       

        # reserved amount

        $withdraw_percentage = $this->withdraw_percentage; 

        $investment_percentage = $this->investment_percentage; 

        $reserved_amount = getPercentageVal($profit_in_hand,$investment_percentage);

        $reserved_amount = number_format((float)$reserved_amount, 2, '.', '');

        # withdrawable amount

        $withdrawable_amount = getPercentageVal($profit_in_hand,$withdraw_percentage);

        $withdrawable_amount = number_format((float)$withdrawable_amount, 2, '.', '');

        # partner net required amount / summation of withdraw required amount 

        $partner_required_amount = DB::table('withdrawls')->where('admin_id', Auth::user()->id)->where('is_disbursed')->sum('required_amount');

        # withdrawable amount each

        $withdrawable_amount_each = getPercentageVal($withdrawable_amount,50);

        $withdrawable_amount_each = number_format((float)$withdrawable_amount_each, 2, '.', '');

        # net partner withdrawable amount

        $withdrawable_amount_each = ($partner_required_amount + $withdrawable_amount_each);

        // dd($withdrawable_amount_each);



        return view('admin.journal.withdraw', compact('net_profit','net_profit_margin','profit_in_hand','reserved_amount','withdrawable_amount','withdrawable_amount_each'));

    }



    public function withdraw_partner_amount(Request $request)

    {

        $admin_id = $request->admin_id;

        $amount = $request->amount;

        $request->validate([

            'admin_id' => 'required',

            'amount' => 'required',

            'entry_date' => 'required',

            'payment_mode' => 'required',

            'chq_utr_no' => 'required_if:payment_mode,cheque,neft',

            'bank_name' => 'required_if:payment_mode,cheque,neft'

        ]);



        $params = $request->except('_token');        

        // dd($params);

        /* Entry in withdrawls */

        $required_amount = ($params['withdrawable_amount'] - $params['amount']);

        // dd($required_amount);



        $voucher_no = 'WITHDRW'.date('YmdHi')."".$params['admin_id'];

        

        $withdrawArr = array(

            'voucher_no' => $voucher_no,

            'admin_id' => $params['admin_id'],

            'entry_date' => $params['entry_date'],

            'withdrawable_percentage' => $this->withdraw_percentage,

            'withdrawl_amount' => $params['amount'],

            'reserved_amount' => $params['reserved_amount'],

            'withdrawable_amount' => $params['withdrawable_amount'],

            'required_amount' => $required_amount,

            'net_profit' => $params['net_profit'],

            'net_profit_margin' => $params['net_profit_margin'],

            'profit_in_hand' => $params['profit_in_hand'],

            'is_disbursed' => 1, 

            'created_at' => date('Y-m-d H:i:s'),

            'updated_at' => date('Y-m-d H:i:s')

        );

        Withdraw::insert($withdrawArr);



        ## Payment Entry ##

        $paymentData = array(            

            'payment_for' => 'debit',

            'voucher_no' => $params['voucher_no'],

            'payment_date' => $params['entry_date'],

            'payment_mode' => $params['payment_mode'],

            'payment_in' => ($params['payment_mode'] != 'cash') ? 'bank' : 'cash' ,

            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 

            'amount' => $params['amount'],

            'bank_name' => $params['bank_name'],

            'chq_utr_no' => $params['chq_utr_no'],

            'narration' => $params['narration'],

            'admin_id' => Auth::user()->id,

            'created_by' => Auth::user()->id,

            'created_at' => date('Y-m-d H:i:s'),

            'updated_at' => date('Y-m-d H:i:s')

        );  

        $payment_id = Payment::insertGetId($paymentData);



        ## Ledger Entry ##

        $purpose_description = "partner withdrawls";



        $ledgerData = array(

            'user_type' => 'partner',

            'transaction_id' => $params['voucher_no'],

            'transaction_amount' => $params['amount'],

            'payment_id' => $payment_id,

            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 

            'is_debit' => 1,

            'entry_date' => $params['entry_date'],

            'admin_id' => Auth::user()->id,

            'purpose' => 'withdrawls',

            'purpose_description' => $purpose_description,

            'created_at' => date('Y-m-d H:i:s'),

            'updated_at' => date('Y-m-d H:i:s')

        );



        Ledger::insert($ledgerData);



        ## Journal Entry ##



        Journal::insert([

            'transaction_amount' => $params['amount'],

            'is_debit' => 1,

            'entry_date' => $params['entry_date'],

            'payment_id' => $payment_id,

            'bank_cash' => ($params['payment_mode'] == 'cash') ? 'cash' : 'bank', 

            'purpose' => 'withdrawls',

            'purpose_description' =>  $purpose_description ,

            'purpose_id' => $params['voucher_no'],

            'created_at' => date('Y-m-d H:i:s'),

            'updated_at' => date('Y-m-d H:i:s')

        ]);

        ## ## ## 



        Session::flash('message', 'Withdraw request submitted successfully'); 

        return redirect()->route('admin.revenue.withdrawls');





        

    }



    public function withdrawls(Request $request)

    {

        $admin_id = Auth::user()->id;

        $auth_type = Auth::user()->type;

        $paginate = 10;

        // echo $auth_type;

        $withdrawls = DB::table('withdrawls AS w')->select('w.*','u.name');



        if($auth_type == 1){

            $withdrawls = $withdrawls->where('w.admin_id',$admin_id);

        }

        

        $withdrawls = $withdrawls->leftJoin('users AS u','u.id','w.admin_id')->orderBy('w.id','desc')->paginate($paginate);

        // dd($withdrawls);

        return view('admin.journal.withdrawls', compact('withdrawls','auth_type','paginate'));

    }



    public function delete_request($id)

    {

        # delete not disbursed withdraw request...

        $withdrawls = DB::table('withdrawls')->find($id);

        if(!empty($withdrawls)){

            if(empty($withdrawls->is_disbursed)){

                DB::table('withdrawls')->where('id',$id)->delete();

                Session::flash('message', 'Delete successfully'); 

                return redirect()->route('admin.revenue.withdrawls');

            } else {

                Session::flash('message', 'Cannot delete ! Amount has been disbursed'); 

                Session::flash('alert-class', 'alert-info');

                return redirect()->route('admin.revenue.index');

            }

        } else {

            Session::flash('message', 'No data found'); 

            Session::flash('alert-class', 'alert-info');

            return redirect()->route('admin.revenue.index');

        }

    }



    public function downloadJournalCSV(Request $request)

    {

        # download journal csv ...

        $from_date = !empty($request->from_date)?$request->from_date:'';

        $to_date = !empty($request->to_date)?$request->to_date:'';

        $bank_cash = !empty($request->bank_cash)?$request->bank_cash:'';



        $data = DB::table('journal AS l')->select('l.*','p.voucher_no','p.payment_in','p.amount AS payment_amount','p.payment_mode','p.chq_utr_no','p.narration');



        $journal_started = DB::table('journal')->min('entry_date');

        

        if(!empty($journal_started)  ){

            // $from_date = ($request->from_date < $journal_started) ? $journal_started : $request->from_date;

            $min_from_date = $journal_started;

        } else{

            $min_from_date = $from_date;

        }

        

        if(!empty($from_date) && !empty($to_date)){

            $data = $data->whereRaw("( l.entry_date BETWEEN '".$from_date."' AND '".$to_date."')");

        } 

        

        if(!empty($bank_cash)){

            $data = $data->where('l.bank_cash', $bank_cash);

        }

        $data = $data->leftJoin('payment AS p','p.id','l.payment_id');

        $data = $data->orderBy('l.entry_date','asc');

        $data = $data->orderBy('l.updated_at','asc');  

        $data = $data->get();



        



        $myArr = array();

        foreach($data  as  $item){

            $myArr[] = array(

                'is_credit' => $item->is_credit,

                'is_debit' => $item->is_debit,

                'purpose' => $item->purpose,

                'purpose_id' => $item->purpose_id,

                'transaction_amount' => $item->transaction_amount,

                'entry_date' => $item->entry_date,

                'bank_cash' => $item->bank_cash

            ); 

            

        }



        $fileName = "vozen-pnl-".date('Ymd',strtotime($from_date))."-".date('Ymd',strtotime($to_date)).".csv";



        // dd($myArr);



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

                $row['Transaction Id / Voucher No'] = $item['purpose_id'];

                $row['Purpose'] = ucwords(str_replace("_"," ",$item['purpose']))." ".$show_payment_mode;                

                $row['Debit']  = replaceMinusSign($debitAmt);

                $row['Credit']    = $creditAmt;

                $row['Closing']  =  replaceMinusSign($net_value)." ".getCrDr($net_value);



                fputcsv($file, array($row['Date'], $row['Transaction Id / Voucher No'],$row['Purpose'], $row['Debit'], $row['Credit'], $row['Closing']));                

            }

            fclose($file);

        };

        return response()->stream($callback, 200, $headers);



        // dd($myArr);

        

    }

}

