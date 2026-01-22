<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\UserInterface;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Supplier;
use App\User;
use App\Models\Store;
use App\Models\StoreNote;
use App\Models\Ledger;
use App\Models\StockLog;
use App\Models\Invoice;
use App\Models\StockAudit;
use App\Models\StockBox;
use App\Models\PaymentCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use DateTime;


class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:web');
    }
    
    /**
     * This method is for admin dashboard
     *
     */
    public function home(Request $request)
    {        
        // die("Hello");
        $data = (object)[];
        $data->users = User::where('type',2)->count(); /* Staff count */
        $data->suppliers = Supplier::count();        
        $data->products = Product::count();
        $data->stores = Store::count();

        $store_sales_data =  array();


        $store_sales_data = DB::table('orders')->select('orders.store_id','stores.store_name','stores.bussiness_name')->selectRaw("SUM(orders.final_amount) AS amount")->leftJoin('stores','stores.id','orders.store_id')->groupBy('orders.store_id')->orderByRaw('SUM(orders.final_amount) DESC')->where('orders.status', '!=', 3)->take(10)->get();
        
        $data->store_sales_data = $store_sales_data;
        $data->last_unpaid_stores = $this->last_unpaid_stores(10);
        // dd($data);
        
        return view('admin.home', compact('data'));
    }
    
    private function last_unpaid_stores($number=10){

        $data = Ledger::select('store_id')->with('store')->where('user_type', 'store')->groupBy('store_id');
        
        $data = $data->get()->toArray();
        $myArr = array();
        foreach($data as $key => $item){
            $getStoreLedgerAmount = getStoreLedgerAmount($item['store_id']);
            $outstanding = $getStoreLedgerAmount['outstanding'];

            $total_payment = PaymentCollection::where('store_id', $item['store_id'])->sum('collection_amount');
            // echo 'total_payment:- '.($total_payment).'<br/>';

            $last_bill_amount = Ledger::where('store_id', $item['store_id'])->select('id','purpose','transaction_id','is_credit','is_debit','transaction_amount','entry_date')->where('is_debit', 1)->orderBy('entry_date', 'desc')->first();

            $total = 0;
            $invoice_date = '';            
            $due_days = 0;
		  if($last_bill_amount){
            if($last_bill_amount->transaction_amount == replaceMinusSign($outstanding)){
                // dd('Hi');
                $invoice_date = $last_bill_amount->entry_date;
            } else {
                $bills = Ledger::where('store_id', $item['store_id'])->select('id','purpose','transaction_id','is_credit','is_debit','transaction_amount','entry_date')->where('is_debit', 1)->orderBy('entry_date')->get()->toArray();
                
                foreach($bills as $key => $bill){
                    $total += $bill['transaction_amount'];  
                    
                    if($total > $total_payment){
                        $invoice_date = $bill['entry_date'];
                        break;
                    }
                }
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
                    
                $finalArr[] = array('store_id'=>$arr['store_id'],'store_name'=>$arr['store_name'],'amount'=>$arr['amount'],'due_days'=>$arr['due_days'],'invoice_date'=>$arr['invoice_date']);                    
                
                
            }
        }
        
        
        usort($finalArr, function($a, $b) {
            return $a['amount'] <=> $b['amount'];
        });

        array_splice($finalArr,$number);
        
        return $finalArr;
        
    }

    

    

    

    
}
