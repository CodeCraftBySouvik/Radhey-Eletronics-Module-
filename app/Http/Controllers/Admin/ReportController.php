<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use App\User;
use App\UserAttendance;
use App\Models\PaymentCollection;
use App\Models\CollectionStaffCommission;
use App\Models\Ledger;
use App\Models\UserCity;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Supplier;
use App\Models\Store;
use App\Models\StoreNote;
use App\Models\StockLog;
use App\Models\Invoice;
use App\Models\InvoiceProduct;
use App\Models\StockAudit;
use App\Models\StockBox;
use App\Models\ReturnBox;
use App\Models\PurchaseOrderBox;
use App\Models\PurchaseOrderProduct;
use App\Models\StockProduct;
use App\Models\PackingslipNew1;
use App\Models\Packingslip;
use App\Models\ReturnProduct;
use App\Models\PurchaseReturnProduct;
use App\Models\PurchaseReturnBox;
use App\Exports\StockReportExport;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CpSpReportExport;
use App\Exports\StoreDuePaymentsExport;
use App\Exports\UserLedgerExport;
use App\Exports\StockLedgerExport;
use App\Exports\SalesAnalysisExport;
use App\Exports\PaymentCollectionReportExport;
use Carbon\Carbon;


class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function cp_sp_report(Request $request)
    {
        # CP / SP Report...
        $paginate = 20;
        $search = !empty($request->search)?$request->search:'';
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d', strtotime("-3 months"));
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');

        $data = Product::select('id','name','cost_price','pcs','sub_cat_id');

        if(!empty($search)){
            $data = $data->where('name','LIKE','%'.$search.'%')->orWhereHas('subCategory', function($subCategory) use($search){
                $subCategory->where('name', 'LIKE', '%'.$search.'%');
            });
        }

        $data = $data->where('cost_price','!=',0)->orderBy('sub_cat_id','asc')->get()->groupBy('sub_cat_id');        
        // dd($data);

        return view('admin.report.cpsp', compact('data','from_date','to_date','paginate','search'));

    }

    public function cp_sp_csv(Request $request)
    {

        $search = !empty($request->search) ? $request->search : '';
        $from_date = !empty($request->from_date) ? $request->from_date : date('Y-m-d', strtotime("-3 months"));
        $to_date = !empty($request->to_date) ? $request->to_date : date('Y-m-d');

        $data = Product::select('id','name','cost_price','pcs','sub_cat_id');

        if (!empty($search)) {
            $data->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                ->orWhereHas('subCategory', function ($subCategory) use ($search) {
                    $subCategory->where('name', 'LIKE', '%' . $search . '%');
                });
            });
        }

        $data = $data->where('cost_price','!=',0)
                    ->orderBy('sub_cat_id','asc')
                    ->get();

        $rows = [];

        foreach ($data as $item) {
            $price = getProductMinMaxSellPrice($from_date, $to_date, $item->id);
            $stockData = checkStockPO($item->id, 0);

            $rows[] = [
                $item->name,
                $item->subCategory->name,
                'XOF. ' . number_format((float)$item->cost_price, 2),
                'XOF. ' . number_format((float)$price['minPrice'], 2),
                'XOF. ' . number_format((float)$price['maxPrice'], 2),
                $item->pcs,
                $stockData['stock'],
            ];
        }

        $fileName = "CP-SP-" . date('Ymd', strtotime($from_date)) . "-" . date('Ymd', strtotime($to_date)) . ".xlsx";

        return Excel::download(new CpSpReportExport($rows), $fileName);
    }

    // public function cp_sp_csv(Request $request)
    // {
    //     # CP / SP CSV Download...

    //     $search = !empty($request->search)?$request->search:'';
    //     $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d', strtotime("-3 months"));
    //     $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');

    //     $data = Product::select('id','name','cost_price','pcs','sub_cat_id');

    //     if(!empty($search)){
    //         $data = $data->where('name','LIKE','%'.$search.'%')->orWhereHas('subCategory', function($subCategory) use($search){
    //             $subCategory->where('name', 'LIKE', '%'.$search.'%');
    //         });
    //     }
    //     $data = $data->where('cost_price','!=',0)->orderBy('sub_cat_id','asc')->get();
    //     // dd($data);

    //     $myArr = array();
    //     foreach($data as $item){
    //         $getProductMinMaxSellPrice = getProductMinMaxSellPrice($from_date,$to_date,$item->id);
    //         $minPrice = $getProductMinMaxSellPrice['minPrice'];
    //         $maxPrice = $getProductMinMaxSellPrice['maxPrice'];
    //         $checkStockPO = checkStockPO($item->id,0);
    //         $stock = $checkStockPO['stock'];
            
    //         $myArr[] = array(
    //             'product' => $item->name,
    //             'subcat_name' => $item->subCategory->name,
    //             'pcs' => $item->pcs,
    //             'costPrice' => 'XOF. '.number_format((float)$item->cost_price, 2, '.', ''),
    //             'minPrice' => 'XOF. '.number_format((float)$minPrice, 2, '.', ''),
    //             'maxPrice' => 'XOF. '.number_format((float)$maxPrice, 2, '.', ''),
    //             'stock' => $stock
    //         ); 
    //     }

    //     // dd($myArr);
    //     $fileName = "CP-SP-".date('Ymd',strtotime($from_date))."-".date('Ymd',strtotime($from_date)).".csv";
    //     $headers = array(
    //         "Content-type"        => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma"              => "no-cache",
    //         "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires"             => "0"
    //     );
       
    //     $columns = array('Product','Subcategory','Cost Price','Min Sell Price','Max Sell Price','Pcs Per Ctn', 'Stock');


    //     $callback = function() use($myArr, $columns) {
    //         $file = fopen('php://output', 'w');
    //         fputcsv($file, $columns);            

    //         foreach ($myArr as $item) {          
    //             $row['Product']  = $item['product'];
    //             $row['Subcategory'] = $item['subcat_name'];
    //             $row['Cost Price'] = $item['costPrice'];
    //             $row['Min Sell Price'] = $item['minPrice'];
    //             $row['Max Sell Price'] = $item['maxPrice'];
    //             $row['Pcs Per Ctn'] = $item['pcs'];
    //             $row['Stock'] = $item['stock'];
                                
    //             fputcsv($file, array($row['Product'], $row['Subcategory'],$row['Cost Price'], $row['Min Sell Price'], $row['Max Sell Price'], $row['Pcs Per Ctn'], $row['Stock'] ));                
    //         }
    //         fclose($file);
    //     };
    //     return response()->stream($callback, 200, $headers);





    // }

    public function store_due_payment(Request $request)
    {
        # Store Due Payments ...

        $store_id = !empty($request->store_id)?$request->store_id:'';
        $days_above = !empty($request->days_above)?$request->days_above:'';
        $amount_above = !empty($request->amount_above)?$request->amount_above:'';
        $sort = !empty($request->sort)?$request->sort:'days_high_to_low';
        $bussiness_name = !empty($request->bussiness_name)?$request->bussiness_name:'';
        $orderby = !empty($request->orderby)?$request->orderby:'desc';
        $orderbyamount = !empty($request->orderbyamount)?$request->orderbyamount:'desc';
        $data = array();

        $data = Ledger::select('store_id')->with('store')->where('user_type', 'store')->groupBy('store_id');
        if(!empty($store_id)){
            $data = $data->where('store_id',$store_id);
        } 
       
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

        
        
        ## ## ## By Default Due Days ASC ## ## ##
        if($sort == 'days_high_to_low'){
            usort($finalArr, function($a, $b) {
                return $a['due_days'] <= $b['due_days'];
            });
        } else if ($sort == 'days_low_to_high'){
            usort($finalArr, function($a, $b) {
                return $a['due_days'] <=> $b['due_days'];
            });
        } else if ($sort == 'amount_high_to_low'){
            usort($finalArr, function($a, $b) {
                return $a['amount'] <=> $b['amount'];
            });
        } else if ($sort == 'amount_low_to_high'){
            usort($finalArr, function($a, $b) {
                return $a['amount'] <= $b['amount'];
            });
        }
        ## ## ## ## ## ## ## ## ## ## ## ## ##

        ## ## ## Custom Pagination ## ## ## ##

        $totalResult = count($finalArr);
        $nthPageArr = $pagedArray = $nthPageArr = array();
        $paginate = 20;
        $nthPageNumber = 0;
        $page = !empty($request->page)?$request->page:1;
        $pageNumber = ($page - 1);
        $pagedArray = !empty($finalArr)?array_chunk($finalArr, $paginate, true):array();
        $nthPageArr = !empty($pagedArray)?$pagedArray[$pageNumber]:array();
        $nthPageNumber = (!empty($pagedArray))?count($pagedArray):0;
        // dd($nthPageNumber);
        ## ## ## ## ## ## ## ## ## ## ## ## ##
        
        return view('admin.report.store-due-payment', compact('store_id','bussiness_name','finalArr','days_above','amount_above','sort','nthPageArr','pagedArray','nthPageNumber','page','paginate','totalResult'));

    }

    public function store_due_csv(Request $request)
    {
        # Store Unpaid CSV...   

        $days_above = !empty($request->days_above)?$request->days_above:'';
        $amount_above = !empty($request->amount_above)?$request->amount_above:'';
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $bussiness_name = !empty($request->bussiness_name)?$request->bussiness_name:'';
        $sort = !empty($request->sort)?$request->sort:'days_high_to_low';
        $data = array();

        $data = Ledger::select('store_id')->with('store')->where('user_type', 'store')->groupBy('store_id');
        if(!empty($store_id)){
            $data = $data->where('store_id',$store_id);
        } 
       
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
        ## ## ## By Default Due Days ASC ## ## ##
        if($sort == 'days_high_to_low'){
            usort($finalArr, function($a, $b) {
                return $a['due_days'] <= $b['due_days'];
            });
        } else if ($sort == 'days_low_to_high'){
            usort($finalArr, function($a, $b) {
                return $a['due_days'] <=> $b['due_days'];
            });
        } else if ($sort == 'amount_high_to_low'){
            usort($finalArr, function($a, $b) {
                return $a['amount'] <=> $b['amount'];
            });
        } else if ($sort == 'amount_low_to_high'){
            usort($finalArr, function($a, $b) {
                return $a['amount'] <= $b['amount'];
            });
        }
        ## ## ## ## ## ## ## ## ## ## ## ## ##

        $fileName = "Vozen-Store-Dues-" . date('Ymd') . ".xlsx";
        return Excel::download(new StoreDuePaymentsExport($finalArr), $fileName);

        // $fileName = "Vozen-Store-Dues-".date('Ymd').".csv";
        // $headers = array(
        //     "Content-type"        => "text/csv",
        //     "Content-Disposition" => "attachment; filename=$fileName",
        //     "Pragma"              => "no-cache",
        //     "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        //     "Expires"             => "0"
        // );
       
        // $columns = array('Store','Due Remaining','Unpaid Amount');


        // $callback = function() use($finalArr, $columns) {
        //     $file = fopen('php://output', 'w');
        //     fputcsv($file, $columns);            

        //     foreach ($finalArr as $item) {    
        //         $amount = $item['amount'];
        //         $row['Store']  = $item['store_name'];
        //         $row['Due Remaining'] = $item['due_days'].' days';
        //         $row['Unpaid Amount'] = replaceMinusSign($amount)." ".getCrDr($amount);
                                
        //         fputcsv($file, array($row['Store'], $row['Due Remaining'],$row['Unpaid Amount']));                
        //     }
        //     fclose($file);
        // };
        // return response()->stream($callback, 200, $headers);



    }

    public function sales_report(Request $request)
    {
        $paginate = !empty($request->paginate)?$request->paginate:25;
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d', strtotime("-15 days"));
        $store_ids = !empty($request->store_ids)?$request->store_ids:'';

        $stores = Store::select('id','bussiness_name')->orderBy('bussiness_name')->get();



        // $orders = Order::select('id','store_id','amount','order_no','created_at')->with('stores:id,store_name,bussiness_name')->with('orderProducts:id,order_id,product_id,product_name,qty,pcs,piece_price,price')->with('packingslip:id,order_id,is_disbursed')->where('status', '!=', 3)->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

        // $count_order = Order::where('status', '!=', 3)->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
        // $total_amount = Order::where('status', '!=', 3)->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
        
        // $storeidc = '';
        // if(!empty($store_ids)){
        //     $storeidc = implode(",",$store_ids);
        //     $orders = $orders->whereIn('store_id',$store_ids);
        //     $count_order = $count_order->whereIn('store_id',$store_ids);
        //     $total_amount = $total_amount->whereIn('store_id',$store_ids);
        // }
        
        // $orders = $orders->orderBy('id','desc')->paginate($paginate);

        // $count_order = $count_order->count();
        // $total_amount = $total_amount->sum('amount');

        $orders = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
        $count_order = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
        $total_amount =  Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

        $storeidc = '';
        if(!empty($store_ids)){
            $storeidc = implode(",",$store_ids);
            $orders = $orders->whereIn('store_id',$store_ids);
            $count_order = $count_order->whereIn('store_id',$store_ids);
            $total_amount = $total_amount->whereIn('store_id',$store_ids);
        }

        $orders = $orders->orderBy('id','desc')->paginate($paginate);

        $count_order = $count_order->count();
        $total_amount = $total_amount->sum('net_price');

        // dd($orders);

        $orders = $orders->appends([
            'page' => $request->page,
            'paginate' => $paginate,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'store_ids' => $store_ids
        ]);

        // dd($orders);
        return view('admin.report.sales', compact('orders','count_order','total_amount','paginate','from_date','to_date','stores','store_ids','storeidc'));

    }

    public function sales_report_pdf(Request $request)
    {
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d', strtotime("-15 days"));
        $storeidc = !empty($request->storeidc)?$request->storeidc:'';

        // $orders = Order::select('id','store_id','amount','order_no','created_at')->with('stores:id,store_name,bussiness_name')->with('orderProducts:id,order_id,product_id,product_name,qty,pcs,piece_price,price')->with('packingslip:id,order_id,is_disbursed')->where('status', '!=', 3)->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

        // if(!empty($storeidc)){
        //     // dd($store_ids);
        //     $store_ids = explode(",",$storeidc);
        //     $orders = $orders->whereIn('store_id',$store_ids);
        // }        
        // $orders = $orders->orderBy('id','desc')->get();

        $orders = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
        $count_order = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
        $total_amount =  Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

        $storeidc = '';
        if(!empty($store_ids)){
            $storeidc = implode(",",$store_ids);
            $orders = $orders->whereIn('store_id',$store_ids);
            $count_order = $count_order->whereIn('store_id',$store_ids);
            $total_amount = $total_amount->whereIn('store_id',$store_ids);
        }
        $orders = $orders->orderBy('id','desc')->get();


        $pdf = Pdf::loadView('admin.report.sales-pdf', compact('orders','to_date','from_date','storeidc'));
        $pdfname = "vozen-sales-".date('Ymd',strtotime($to_date))."-".date('Ymd',strtotime($from_date));
        return $pdf->download($pdfname.'.pdf');

    }

    public function sales_report_csv(Request $request)
{
    $to_date   = !empty($request->to_date) ? $request->to_date : date('Y-m-d');
    $from_date = !empty($request->from_date) ? $request->from_date : date('Y-m-d', strtotime("-15 days"));
    $storeidc  = !empty($request->storeidc) ? $request->storeidc : '';

    $orders = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date]);

    if (!empty($storeidc)) {
        $store_ids = explode(",", $storeidc);
        $orders = $orders->whereIn('store_id', $store_ids);
    }

    $orders = $orders->orderBy('id', 'desc')->get();

    $myArr = [];

    foreach ($orders as $item) {

        $ordProdArr = [];

        foreach ($item->products as $pro) {
            $ordProdArr[] = [
                'product_name' => $pro->product_name,
                'piece_price'  => $pro->single_product_price,
                'qty'          => $pro->quantity,
            ];
        }

        $myArr[] = [
            'date'        => date('d/m/Y', strtotime($item->created_at)),
            'order_no'    => $item->order->order_no,
            'invoice_no'  => $item->invoice_no,
            'store'       => !empty($item->store->bussiness_name)
                                ? $item->store->bussiness_name
                                : $item->stores->store_name,
            'amount'      => 'XOF. ' . number_format((float)$item->net_price, 2, '.', ''),
            'products'    => $ordProdArr,
        ];
    }

    $fileName = "vozen-sales-"
        . date('Ymd', strtotime($to_date))
        . "-"
        . date('Ymd', strtotime($from_date))
        . ".xlsx";

    return Excel::download(new SalesReportExport($myArr), $fileName);
}

    // public function sales_report_csv(Request $request)
    // {
    //     $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
    //     $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d', strtotime("-15 days"));
    //     $storeidc = !empty($request->storeidc)?$request->storeidc:'';

    //     // $orders = Order::select('id','store_id','amount','order_no','created_at')->with('stores:id,store_name,bussiness_name')->with('orderProducts:id,order_id,product_id,product_name,qty,pcs,piece_price,price')->with('packingslip:id,order_id,is_disbursed')->where('status', '!=', 3)->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

    //     // if(!empty($storeidc)){
    //     //     // dd($store_ids);
    //     //     $store_ids = explode(",",$storeidc);
    //     //     $orders = $orders->whereIn('store_id',$store_ids);
    //     // }
        
    //     // $orders = $orders->orderBy('id','desc')->get();

    //     $orders = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
    //     $count_order = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
    //     $total_amount =  Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

    //     $storeidc = '';
    //     if(!empty($store_ids)){
    //         $storeidc = implode(",",$store_ids);
    //         $orders = $orders->whereIn('store_id',$store_ids);
    //         $count_order = $count_order->whereIn('store_id',$store_ids);
    //         $total_amount = $total_amount->whereIn('store_id',$store_ids);
    //     }
    //     $orders = $orders->orderBy('id','desc')->get();
        
    //     $myArr = array();
    //     foreach($orders as $item){
            
    //         $orderProducts = $item->products;
    //         foreach($orderProducts as $pro){
    //             $ordProdArr[] = array(
    //                 'product_name' => $pro->product_name,
    //                 'piece_price' => $pro->single_product_price,
    //                 'qty' => $pro->quantity
    //             );
    //         }
    //         $myArr[] = array(
    //             'date' => date('d/m/Y', strtotime($item->created_at)),
    //             'order_no' => $item->order->order_no,
    //             'invoice_no' => $item->invoice_no,
    //             'store' => !empty($item->store->bussiness_name)?$item->store->bussiness_name:$item->stores->store_name,
    //             'amount' => 'XOF. '.number_format((float)$item->net_price, 2, '.', ''),
    //             'products' => $ordProdArr
    //         ); 
    //     }

    //     // dd($myArr);
        


    //     $fileName = "wmtools-sales-".date('Ymd',strtotime($to_date))."-".date('Ymd',strtotime($from_date)).".csv";
    //     $headers = array(
    //         "Content-type"        => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma"              => "no-cache",
    //         "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires"             => "0"
    //     );

    //     $columns = array('Date','Order No / Invoice No','Store','Amount');

    //     $callback = function() use($myArr, $columns) {
    //         $file = fopen('php://output', 'w');
    //         fputcsv($file, $columns);
            
    //         foreach ($myArr as $item) {    
    //             $row['Date']  = $item['date'];
    //             $row['Order No / Invoice No'] = $item['order_no'].' / '.$item['invoice_no'];
    //             $row['Store'] = $item['store'];                
    //             $row['Amount'] = $item['amount'];
                                
    //             fputcsv($file, array($row['Date'], $row['Order No / Invoice No'], $row['Store'], $row['Amount']));                
    //         }
    //         fclose($file);
    //     };
    //     return response()->stream($callback, 200, $headers);
    // }

   public function sales_analysis(Request $request)
    {
        # sales analysis...
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-01', strtotime(date('Y-m-d')));
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
        $store_ids = !empty($request->store_ids)?$request->store_ids:'';
        $type = !empty($request->type)?$request->type:'';
        $product_ids = !empty($request->product_ids)?$request->product_ids:'';
        
        $min_from_date = Invoice::min('created_at');
        $min_from_date = date('Y-m-d', strtotime($min_from_date));
        
        

        
        $proidc = !empty($request->proidc)?$request->proidc:'';
        if(!empty($proidc)){
            $product_ids = explode(",",$proidc);
        }
        $storeidc = '';
        $data = array();
        if(!empty($product_ids)){
            // $invIds = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

            
            $data = InvoiceProduct::select('*');
            $proidc = implode(",",$product_ids);
            $data = $data->whereIn('product_id',$product_ids);  
            $data = $data->whereHas('invoice', function($invoice) use ($from_date,$to_date){
                $invoice->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
            });
            
            if(!empty($store_ids)){
                $storeidc = implode(",",$store_ids);
                // $invIds = $invIds->whereIn('store_id',$store_ids);   
                $data = $data->whereHas('invoice', function($store) use ($store_ids){
                    $store->whereIn('store_id',$store_ids);   
                });
            }
            
            // if(!empty($invIds)){
            //     $data = $data->whereIn('invoice_id',$invIds);
            // }   
            $data = $data->orderBy('product_id','asc')->get()->groupBy('product_id');              
        }

       
        
        
        // $data = InvoiceProduct::whereIn('invoice_id',$invIds)->where('product_id',$product_id)->get();

        // dd($data);

        return view('admin.report.sales-analysis', compact('type','from_date','to_date','min_from_date','data','store_ids','storeidc','product_ids','proidc'));

    }
    
    public function all_product_sales_analysis(Request $request){
        # sales analysis...
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-01', strtotime(date('Y-m-d')));
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
        $product_ids = !empty($request->product_ids)?$request->product_ids:'';
        $store_ids = !empty($request->store_ids)?$request->store_ids:'';
        
        $min_from_date = Invoice::min('created_at');
        $min_from_date = date('Y-m-d', strtotime($min_from_date));
        
        $storeidc = '';
        $data = array();
        if(!empty($product_ids)){
            // $invIds = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

            
            $data = InvoiceProduct::select('*');
            $proidc = implode(",",$product_ids);
            $data = $data->whereIn('product_id',$product_ids);  
            $data = $data->whereHas('invoice', function($invoice) use ($from_date,$to_date){
                $invoice->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
            });
            
            if(!empty($store_ids)){
                $storeidc = implode(",",$store_ids);
                // $invIds = $invIds->whereIn('store_id',$store_ids);   
                $data = $data->whereHas('invoice', function($store) use ($store_ids){
                    $store->whereIn('store_id',$store_ids);   
                });
            }
            
            // if(!empty($invIds)){
            //     $data = $data->whereIn('invoice_id',$invIds);
            // }   
            $data = $data->orderBy('product_id','asc')->get()->groupBy('product_id');              
        }
        return view('admin.report.all-product-sales-analysis', compact('from_date','to_date','min_from_date','data','store_ids','storeidc','product_ids','proidc'));
    }

    public function sales_analysis_csv(Request $request)
    {
        # sales analysis csv...
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-01', strtotime(date('Y-m-d')));
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');

        $proidc = !empty($request->proidc)?$request->proidc:'';
        $storeidc = !empty($request->storeidc)?$request->storeidc:'';
        $product_ids = [];
        if(!empty($proidc)){
            $product_ids = explode(",",$proidc);
        }
        $store_ids = [];
        if(!empty($storeidc)){
            $store_ids = explode(",",$storeidc);
        }
        
        $data = array();
        if(!empty($product_ids)){
            // $invIds = Invoice::whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);

            
            $data = InvoiceProduct::select('*');
            $proidc = implode(",",$product_ids);
            $data = $data->whereIn('product_id',$product_ids);  
            $data = $data->whereHas('invoice', function($invoice) use ($from_date,$to_date){
                $invoice->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date]);
            });
            
            if(!empty($store_ids)){ 
                $data = $data->whereHas('invoice', function($store) use ($store_ids){
                    $store->whereIn('store_id',$store_ids);   
                });
            }
            
            $data = $data->orderBy('product_id','asc')->get();              
        }

        // dd($data);


        $myArr = array();
        foreach($data as $item){
            $order_no = $item->invoice->order->order_no;
            $invoice_no = $item->invoice->invoice_no;
            $order_no_invoice_no = $order_no." / ".$invoice_no;
            // $total_price = ($item->pcs * $item->single_product_price);
            $myArr[] = array(
                'date' => date('d/m/Y', strtotime($item->created_at)),
                'store' => $item->invoice->store->bussiness_name,
                'product' => $item->product->name,
                'order_no_invoice_no' => $order_no_invoice_no,
                'total_ctns' => $item->quantity.' ctns',
                'total_pcs' => $item->pcs.' pcs',
                'piece_price' => 'XOF. '.number_format((float)$item->single_product_price, 2, '.', ''),
                'total_price' => 'XOF. '.number_format((float)$item->total_price, 2, '.', '')
            );
        }

        // dd($myArr);
        $fileName = "vozen-sales-analysis-".date('Ymd',strtotime($from_date))."-".date('Ymd',strtotime($to_date)).".xlsx";

        return Excel::download(new SalesAnalysisExport($from_date, $to_date, $product_ids, $store_ids),
        $fileName
    );
        // $headers = array(
        //     "Content-type"        => "text/csv",
        //     "Content-Disposition" => "attachment; filename=$fileName",
        //     "Pragma"              => "no-cache",
        //     "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        //     "Expires"             => "0"
        // );
       
        // $spaceColumn1 = array('','','','','','','','','');
        // $columns = array('#','Date','Product','Store','Order No / Invoice No','Total Cartons','Total Pieces','Rate','Total');
        // $fromDateColumn = array('','From Date:- '.date('d/m/Y', strtotime($from_date)).'');
        // $toDateColumn = array('','To Date:- '.date('d/m/Y', strtotime($to_date)).'');


        // $callback = function() use($myArr, $spaceColumn1,$fromDateColumn,$toDateColumn,$columns) {
        //     $file = fopen('php://output', 'w');
        //     fputcsv($file, $spaceColumn1);
        //     fputcsv($file, $fromDateColumn);
        //     fputcsv($file, $toDateColumn);
        //     fputcsv($file, $columns);
        //     $i=1;
        //     foreach ($myArr as $item) {
        //         $row['#'] = $i;      
        //         $row['Date']  = $item['date'];
        //         $row['Product'] = $item['product'];
        //         $row['Store'] = $item['store'];
        //         $row['Order No / Invoice No'] = $item['order_no_invoice_no'];
        //         $row['Total Cartons'] = $item['total_ctns'];
        //         $row['Total Pieces'] = $item['total_pcs'];
        //         $row['Rate'] = $item['piece_price'];
        //         $row['Total'] = $item['total_price'];
                                
        //         fputcsv($file, array($row['#'] ,$row['Date'], $row['Product'], $row['Store'],$row['Order No / Invoice No'], $row['Total Cartons'], $row['Total Pieces'], $row['Rate'], $row['Total'] )); 
                
        //         $i++;
        //     }
        //     fclose($file);
        // };
        // return response()->stream($callback, 200, $headers);

    }

    
    public function staff_commission(Request $request)
    {
        # code...
        $salesman = User::where('designation', 1)->where('status', 1)->get();
        // $city_id = !empty($request->city_id)?$request->city_id:'';
        // $city_name = !empty($request->city_name)?$request->city_name:'';
        $month = !empty($request->month)?$request->month:'';
        $user_id = !empty($request->user_id)?$request->user_id:'';
        

        $data = DB::table('collection_staff_commissions AS csc')->select('csc.*','u.name')->leftJoin('users AS u','u.id','csc.user_id');

        if(!empty($month)){
            $month_explode = explode("-",$month);
            $year_val = $month_explode[0];
            $month_val = $month_explode[1];

            $data = $data->where('csc.year_val',$year_val)->where('csc.month_val',$month_val);
        }
        
        if(!empty($user_id)){
            $data = $data->where('user_id', $user_id);
        }
        
        
        $data = $data->get();
        
        return view('admin.report.collect-comm', compact('salesman','month','user_id','data'));
    }

    /*public function staff_commission(Request $request)
    {
        # code...
        $salesman = User::where('designation', 1)->where('status', 1)->get()->toArray();
        $city_id = !empty($request->city_id)?$request->city_id:'';
        $city_name = !empty($request->city_name)?$request->city_name:'';
        $month = !empty($request->month)?$request->month:'';
        $firstday = $lastday = '';
        $payment_collection = $monthly_collection_target_value = $targeted_collection_amount_commission = $commission_on_amount = $final_commission_amount = 0;
        $target_status = "";
        $noTargetValueAdded = false;
        $user_city = array();
        if(!empty($month)){
            $getFirstLastDayMonth = getFirstLastDayMonth($month);
            $firstday = $getFirstLastDayMonth['firstday'];
            $lastday = $getFirstLastDayMonth['lastday'];
                        
        }
        
        return view('admin.report.collect-comm', compact('salesman','month','firstday','lastday','city_id','city_name'));
    }*/

    public function monthly_commissionable_collections($user_id,$month,$year){
        $data = DB::select("SELECT i.invoice_no,i.net_price,p.payment_date,p.voucher_no,e.collect_within_days,c.name AS city_name,s.bussiness_name,e.invoice_paid_amount,e.month_val FROM `staff_collection_commission_eligibility` AS e LEFT JOIN users AS u ON u.id = e.user_id LEFT JOIN invoice i ON i.id = e.invoice_id LEFT JOIN payment p ON p.id = e.payment_id LEFT JOIN cities c ON c.id = e.city_id LEFT JOIN stores s ON s.id = e.store_id WHERE e.month_val = '".$month."' AND e.year_val = '".$year."' AND e.city_id IN (SELECT city_id FROM user_cities WHERE user_cities.user_id = ".$user_id." ) ORDER BY p.payment_date ASC");

        // dd($data);

        // return view('admin.report.comm-details', compact('data'));

        $myArr = array();
        foreach($data as $item){
            
            $myArr[] = array(
                'payment_date' => date('d/m/Y', strtotime($item->payment_date)),
                'bussiness_name' => $item->bussiness_name,
                'city_name' => $item->city_name,
                'collect_within_days' => $item->collect_within_days,
                'voucher_no' => $item->voucher_no,
                'invoice_no' => '#'.$item->invoice_no,
                'invoice_amount' => 'XOF. '.number_format((float)$item->net_price, 2, '.', ''),
                'invoice_paid_amount' => 'XOF. '.number_format((float)$item->invoice_paid_amount, 2, '.', '')
            ); 
        }

        
        $userName = getSingleAttributeTable('users',$user_id,'name');
        $fileName = "vozen-comm-collects-".$userName."-".$month."-".$year.".csv";
        // dd($fileName);
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );
       
        $columns = array('#' ,'Payment Date','Store','City','Invoice No','Invoice Amount','Payment Voucher','Collect Within Days','Invoice Covered Amount');

        $column1 = array();
        $columnUserName = array('','Salesman:- ',$userName);
        $columnMonthYear = array('','Month:- ',date('M Y', strtotime($year.'-'.$month)));
        $column2 = array();
        $callback = function() use($myArr, $column1,$columnUserName,$columnMonthYear,$column2,$columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $column1);
            fputcsv($file, $columnUserName);
            fputcsv($file, $columnMonthYear);
            fputcsv($file, $column2);
            fputcsv($file, $columns);

            $i = 1;
            foreach ($myArr as $item) {       
                $row['#'] = $i;   
                $row['Payment Date']  = $item['payment_date'];
                $row['Store'] = $item['bussiness_name'];
                $row['City'] = $item['city_name'];
                $row['Invoice No'] = $item['invoice_no'];
                $row['Invoice Amount'] = $item['invoice_amount'];
                $row['Payment Voucher'] = $item['voucher_no'];
                $row['Collect Within Days'] = $item['collect_within_days'];
                $row['Invoice Covered Amount'] = $item['invoice_paid_amount'];
                
                                
                fputcsv($file, array($row['#'] ,$row['Payment Date'], $row['Store'],$row['City'], $row['Invoice No'], $row['Invoice Amount'], $row['Payment Voucher'], $row['Collect Within Days'], $row['Invoice Covered Amount'] ));        
                
                $i++;
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);

        
    }

    public function save_commission_ledger(Request $request)
    {
        # Add Commission Ledger...
        $params = $request->except('_token');
        
        $user_id = $params['user_id'];
        $year_val = $params['year_val'];
        $month_val = $params['month_val'];
        $params['unique_id'] = "COMM".$year_val."".$month_val."".str_pad($user_id,4,"0",STR_PAD_LEFT);
        // dd($params);

        $user_cities = UserCity::where('user_id',$user_id)->get()->pluck('city_id')->toArray();
        // $user_cities = array_values($user_cities);  
        $cities = !empty($user_cities)?json_encode($user_cities):NULL;
        $city_ids = !empty($params['city_ids'])?json_encode($params['city_ids']):NULL;
        // dd($cities);
        $params['cities'] = $cities;
        $params['collection_cities'] = $city_ids;
        unset($params['city_ids']);
        // dd($params);
        
        $checkExist = CollectionStaffCommission::where('user_id',$user_id)->where('year_val',$year_val)->where('month_val',$month_val)->first();
        if(empty($checkExist)){
            ## Insert the new record
            $collection_staff_commission_id = CollectionStaffCommission::insertGetId($params);

            ## Add In Ledger
            $ledgerArr = array(
                'user_type' => 'staff',
                'staff_id' => $user_id,
                'collection_staff_commission_id' => $collection_staff_commission_id,
                'transaction_id' => $params['unique_id'],
                'transaction_amount' => $params['final_commission_amount'],
                'is_credit' => 1,
                'entry_date' => date('Y-m-t', strtotime($year_val.'-'.$month_val)),
                'purpose' => 'payment_collection_commission',
                'purpose_description' => 'Monthly Payment Collection Commission',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            Ledger::insert($ledgerArr);

        } else {
            ## Update other fields
            CollectionStaffCommission::where('id',$checkExist->id)->update([
                'collection_amount' => $params['collection_amount'],
                'monthly_collection_target_value' => $params['monthly_collection_target_value'],
                'commission_on_amount' => $params['commission_on_amount'],
                'targeted_collection_amount_commission' => $params['targeted_collection_amount_commission'],
                'final_commission_amount' => $params['final_commission_amount'],
                'cities' => $params['cities'],
                'collection_cities' => $params['collection_cities'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            ## Add In Ledger
            $ledgerArr = array(
                'transaction_amount' => $params['final_commission_amount'],                
                'updated_at' => date('Y-m-d H:i:s')
            );

            Ledger::where('collection_staff_commission_id',$checkExist->id)->update($ledgerArr);

            
        }

        Session::flash('message', 'Saved in ledger successfully'); 
        return redirect()->route('admin.report.staff-commission',['month'=>$year_val.'-'.$month_val]);
    }

    public function stock_report(Request $request)
    {
        $search = !empty($request->search)?$request->search:'';
        $paginate = !empty($request->paginate)?$request->paginate:25;
        $products = Product::select('id','name','cost_price','pcs')->with('count_stock');
        $count_products = Product::select('id','name');

        if(!empty($search)){
            $products = $products->where('name','LIKE','%'.$search.'%');
            $count_products = $products->where('name','LIKE','%'.$search.'%');
        }
        
        $products = $products->orderBy('name')->paginate($paginate);
        $count_products = $count_products->count();
        $products = $products->appends([
            'search' => $search,
            'paginate' => $paginate
        ]);
        // dd($products);
        return view('admin.report.stock', compact('products','paginate','count_products','search'));
    }

    // public function stock_report_csv(Request $request)
    // {
    //     $search = !empty($request->search)?$request->search:'';
    //     $products = Product::select('id','name','cost_price','pcs')->with('count_stock');
        
    //     if(!empty($search)){
    //         $products = $products->where('name','LIKE','%'.$search.'%');
    //         $count_products = $products->where('name','LIKE','%'.$search.'%');
    //     }
        
    //     $products = $products->orderBy('name')->get();

    //     $myArr = array();
    //     foreach($products as $product){
    //         $getStockPriceQty = getStockPriceQty($product->id);
    //         $sumPiecePrice = $getStockPriceQty['sumPiecePrice'];
    //         $myArr[] = array(
    //             'product' => $product->name,
    //             'count_stock' => count($product->count_stock),
    //             'count_pcs' =>  ($product->pcs * count($product->count_stock)),
    //             // 'cp' => $product->cost_price
    //             'stock_price' => $sumPiecePrice
    //         ); 
    //     }

    //     // dd($myArr);
    //     $fileName = "wmtools-stock-".date('Ymd').".csv";
    //     $headers = array(
    //         "Content-type"        => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma"              => "no-cache",
    //         "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires"             => "0"
    //     );

    //     $columns = array('Product','Total No Of Cartons','Total No Of Pieces','Total Stock Amount');

    //     $callback = function() use($myArr, $columns) {
    //         $file = fopen('php://output', 'w');
    //         fputcsv($file, $columns);
            
    //         foreach ($myArr as $item) {    
    //             $row['Product']  = $item['product'];
    //             $row['Total No Of Cartons'] = $item['count_stock'];
    //             $row['Total No Of Pieces'] = $item['count_pcs'];
    //             $row['Total Stock Amount'] = 'XOF. '.number_format((float)$item['stock_price'], 2, '.', '').'';
                
    //             fputcsv($file, array($row['Product'], $row['Total No Of Cartons'], $row['Total No Of Pieces'], $row['Total Stock Amount']));                
    //         }
    //         fclose($file);
    //     };
    //     return response()->stream($callback, 200, $headers);

    // }

    public function stock_report_csv(Request $request)
    {
        $search = !empty($request->search) ? $request->search : '';

        $products = Product::select('id','name','cost_price','pcs')
            ->with('count_stock');

        if (!empty($search)) {
            $products = $products->where('name', 'LIKE', '%' . $search . '%');
        }

        $products = $products->orderBy('name')->get();

        $myArr = [];

        foreach ($products as $product) {

            $getStockPriceQty = getStockPriceQty($product->id);
            $sumPiecePrice   = $getStockPriceQty['sumPiecePrice'];

            $myArr[] = [
                'product'     => $product->name,
                'count_stock' => count($product->count_stock),
                'count_pcs'   => ($product->pcs * count($product->count_stock)),
                'stock_price' => $sumPiecePrice,
            ];
        }

        $fileName = "vozen-stock-" . date('Ymd') . ".xlsx";

        return Excel::download(new StockReportExport($myArr), $fileName);
    }

    public function stock_ledger(Request $request)
    {
        # product stock log...
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-01', strtotime(date('Y-m-d')));
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
        $product_ids = !empty($request->product_ids)?$request->product_ids:array();

        $min_from_date = '2023-01-25';
        $data = array();
        $proidc = '';

        // dd($product_ids);

        if(!empty($product_ids)){
            $proidc = implode(",",$product_ids);
            $data = StockLog::whereIn('product_id',$product_ids)->whereBetween('entry_date', [$from_date,$to_date])->orderBy('entry_date')->orderBy('created_at')->get()->groupBy('product_id');
            // $before_date = date($from_date, strtotime("-1 day"));
            

        }
        
        // dd($data);


        return view('admin.report.stock-ledger', compact('from_date','to_date','min_from_date','data','product_ids','proidc'));
    }

    public function stock_ledger_csv(Request $request)
    {
        # product stock log...
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-01', strtotime(date('Y-m-d')));
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
       
        $proidc = !empty($request->proidc)?$request->proidc:'';
        if(!empty($proidc)){
            $product_ids = explode(",",$proidc);
        }

        $min_from_date = '2023-01-25';
        $data = array();

        if(!empty($product_ids)){
            $proidc = implode(",",$product_ids);
            $data = StockLog::whereIn('product_id',$product_ids)->whereBetween('entry_date', [$from_date,$to_date])->orderBy('product_id')->orderBy('entry_date')->orderBy('created_at')->get();
            // $before_date = date($from_date, strtotime("-1 day"));
        }

        // dd($data);

        $myArr = array();        
        foreach($data as $item){
            $purpose = $particular = "";
            $in_quantity = $out_quantity = '';
            if($item->type == 'in'){
                
                $in_quantity = $item->quantity;
                $purpose = "GOODS RECEIVED";
                $particular = "GRN / ".$item->stock->grn_no;
            } 
            if($item->type == 'out'){
                if(!empty($item->packingslip)){
                    $particular = "PACKING SLIP / ".$item->packingslip->slipno;
                } else if (!empty($item->purchase_return)) {
                    $particular = "PURCHASE RETURN / ".$item->purchase_return->order_no;
                }
                $out_quantity = $item->quantity;
                $purpose = "GOODS DISBURSED";
                
            }
            $opening_stock = openingStock($item->product_id,$from_date);
            // $obArr = array(
            //     'entry_date' => $from_date,
            //     'product' => $item->product->name,
            //     'purpose' => 'OPENING BALANCE',
            //     'particular' => '',
            //     'piece_price' => '',
            //     'in' => '',
            //     'out' => '',
            //     'type' => 'in',
            //     'quantity' => $opening_stock
            // );
            $myArr[] = array(
                'entry_date' => $item->entry_date,
                'product' => $item->product->name,
                'purpose' => $purpose,
                'particular' => $particular,
                'piece_price' => $item->piece_price,
                'in' => $in_quantity,
                'out' => $out_quantity,
                'type' => $item->type,
                'quantity' => $item->quantity
            ); 
        }

        

        // array_unshift($myArr,$obArr);

        // dd($myArr);
        $fileName = "vozen-stockledger-".date('Ymd',strtotime($from_date))."-".date('Ymd',strtotime($to_date)).".xlsx";

        return Excel::download(new StockLedgerExport($from_date, $to_date , $product_ids), $fileName);

        // dd($fileName);
        // $headers = array(
        //     "Content-type"        => "text/csv",
        //     "Content-Disposition" => "attachment; filename=$fileName",
        //     "Pragma"              => "no-cache",
        //     "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        //     "Expires"             => "0"
        // );

        // $spaceColumn1 = array('','','','','','','');
        // $fromDateColumn = array('','From: '.date('d/m/Y', strtotime($from_date)));
        // $toDateColumn = array('','From: '.date('d/m/Y', strtotime($to_date)));
        // $columns = array('Date','Product','Purpose','Paticular','Rate','In','Out');

        // // dd($myArr);

        // $callback = function() use($myArr, $spaceColumn1,$fromDateColumn,$toDateColumn,$columns) {
        //     $file = fopen('php://output', 'w');
        //     fputcsv($file, $spaceColumn1);
        //     fputcsv($file, $fromDateColumn);
        //     fputcsv($file, $toDateColumn);
        //     fputcsv($file, $columns);
            
        //     $net_quantity = 0; 
        //     foreach ($myArr as $item) {   
                

        //         $row['Date']  = date('d/m/Y', strtotime($item['entry_date']));
        //         $row['Product'] = $item['product'];
        //         $row['Purpose'] = $item['purpose'];
        //         $row['Paticular'] = $item['particular'];
        //         $row['Rate'] = !empty($item['piece_price'])?'XOF. '.number_format((float)$item['piece_price'], 2, '.', ''):'';
        //         $row['In'] = $item['in'];
        //         $row['Out'] = $item['out'];
                                
        //         fputcsv($file, array($row['Date'],$row['Product'], $row['Purpose'], $row['Paticular'], $row['Rate'], $row['In'], $row['Out'], ));                
        //     }
        //     fclose($file);
        // };
        // return response()->stream($callback, 200, $headers);

    }

    public function payment_receipt_report(Request $request)
    {
        $paginate = !empty($request->paginate)?$request->paginate:25;
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d', strtotime("-15 days"));
        $filter_by = !empty($request->filter_by)?$request->filter_by:'';
        $store_ids = !empty($request->store_ids)?$request->store_ids:'';
        $city_ids = !empty($request->city_ids)?$request->city_ids:'';
        if(Auth::user()->designation == NULL){
            $bank_cash = !empty($request->bank_cash)?$request->bank_cash:'';
        } else {
            $bank_cash = 'bank';
        }
        

        $stores = Store::select('id','bussiness_name')->orderBy('bussiness_name')->get();


        $data = Ledger::with('store:id,store_name,bussiness_name')->where('purpose', 'payment_receipt')->whereBetween('entry_date', [$from_date,$to_date]);
        $count_data = Ledger::where('purpose','payment_receipt')->whereBetween('entry_date', [$from_date,$to_date]);
        $sum_data = Ledger::where('purpose','payment_receipt')->whereBetween('entry_date', [$from_date,$to_date]);

        $storeidc = '';
        if(!empty($store_ids)){
            $storeidc = implode(",",$store_ids);
            $data = $data->whereIn('store_id',$store_ids);
            $count_data = $count_data->whereIn('store_id',$store_ids);
            $sum_data = $sum_data->whereIn('store_id',$store_ids);
        }
        $citydc = '';
        if(!empty($city_ids)){
            $citydc = implode(",",$city_ids);
            $data = $data->whereHas('store', function($city) use($city_ids){
                $city->whereIn('city_id',$city_ids);
            });
            $count_data = $count_data->whereHas('store', function($city) use($city_ids){
                $city->whereIn('city_id',$city_ids);
            });
            $sum_data = $sum_data->whereHas('store', function($city) use($city_ids){
                $city->whereIn('city_id',$city_ids);
            });
        }

        if(!empty($bank_cash)){
            $data = $data->where('bank_cash', $bank_cash);
            $count_data = $count_data->where('bank_cash', $bank_cash);
            $sum_data = $sum_data->where('bank_cash', $bank_cash);
        }
        
        $data = $data->orderBy('entry_date','desc')->paginate($paginate);
        $count_data = $count_data->count();
        $sum_data = $sum_data->sum('transaction_amount');

        $data = $data->appends([
            'page' => $request->page,
            'paginate' => $paginate,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'bank_cash' => $bank_cash,
            'filter_by' => $filter_by,
            'store_ids' => $store_ids,
            'city_ids' => $city_ids
        ]);

        // dd($data);

        return view('admin.report.payment-receipt', compact('data','paginate','from_date','to_date','store_ids','stores','city_ids','citydc','count_data','sum_data','storeidc','bank_cash','filter_by'));
    }

    public function payment_receipt_report_csv(Request $request)
    {        
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d', strtotime("-15 days"));
        $storeidc = !empty($request->storeidc)?$request->storeidc:'';
        $citydc = !empty($request->citydc)?$request->citydc:'';
        $bank_cash = !empty($request->bank_cash)?$request->bank_cash:'';

        $data = Ledger::with('store:id,store_name,bussiness_name')->where('purpose', 'payment_receipt')->whereBetween('entry_date', [$from_date,$to_date]);        

        if(!empty($storeidc)){
            $store_ids = explode(",",$storeidc);
            $data = $data->whereIn('store_id',$store_ids);
        }

        if(!empty($citydc)){
            $city_ids = explode(",",$citydc);
            $data = $data->whereHas('store', function($city) use($city_ids){
                $city->whereIn('city_id',$city_ids);
            });
        }

        if(!empty($bank_cash)){
            $data = $data->where('bank_cash', $bank_cash);
        }
        
        $data = $data->orderBy('entry_date','desc')->get();

        // dd($citydc);

        $myArr = array();
        foreach($data as $item){
            
            $myArr[] = array(
                'date' => date('d/m/Y', strtotime($item->entry_date)),
                'voucher_no' => $item->transaction_id,
                'store' => !empty($item->store->bussiness_name)?$item->store->bussiness_name:$item->store->store_name,
                'amount' => 'XOF. '.number_format((float)$item->transaction_amount, 2, '.', '')
            ); 
        }

        
        
        $fileName = "vozen-paymentcollection-".date('Ymd',strtotime($to_date))."-".date('Ymd',strtotime($from_date)).".xlsx";
         return Excel::download(
            new PaymentCollectionReportExport($myArr),
            $fileName
        );
        // $headers = array(
        //     "Content-type"        => "text/csv",
        //     "Content-Disposition" => "attachment; filename=$fileName",
        //     "Pragma"              => "no-cache",
        //     "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        //     "Expires"             => "0"
        // );

        // $columns = array('Date','Voucher No','Store','Amount');

        // $callback = function() use($myArr, $columns) {
        //     $file = fopen('php://output', 'w');
        //     fputcsv($file, $columns);

        //     foreach ($myArr as $item) {    
        //         $row['Date']  = $item['date'];
        //         $row['Order No'] = $item['voucher_no'];
        //         $row['Store'] = $item['store'];                
        //         $row['Amount'] = $item['amount'];
                                
        //         fputcsv($file, array($row['Date'], $row['Order No'], $row['Store'], $row['Amount']));                
        //     }
        //     fclose($file);
        // };
        // return response()->stream($callback, 200, $headers);
        

    }

    public function save_current_stock(Request $request)
    {
        # Save Current Stock...

        $get_todays_last_log = StockAudit::where('entry_date', date('Y-m-d'))->first();

        // dd($get_todays_last_log);

        if(!empty($get_todays_last_log)){
            
            $products = StockAudit::where('entry_date', date('Y-m-d'))->get()->toArray();
            foreach($products as $product){
                $currentProd = Product::select('id','name')->with('count_stock:id,product_id,pcs,barcode_no')->find($product['product_id']);
                StockAudit::where('product_id', $product['product_id'])->update([
                    'quantity' => count($currentProd->count_stock),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        } else {
            $products = Product::select('id','name')->with('count_stock:id,product_id,pcs,barcode_no')->orderBy('name')->where('status', 1)->get()->toArray();

            // $products = StockBox::select('id','product_id','barcode_no',DB::raw("COUNT(id) AS qty"))->with('product:id,name')->where('is_stock_out', 0)->groupBy('product_id')->orderBy('product_id')->get()->toArray();
            foreach($products as $product){
                StockAudit::insert([
                    'product_id' => $product['id'],
                    'quantity' => count($product['count_stock']),
                    'entry_date' => date('Y-m-d'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }
        
        // dd($products);
        Session::flash('message', 'Current Stock Logged Successfully'); 
        return redirect()->route('admin.stockaudit.list'); 

    }

    public function stock_audit_csv(Request $request)
    {
        # Stock Audit CSV...
        // dd('Hi');
        $entry_date = !empty($request->entry_date)?$request->entry_date:'';
        $search = !empty($request->search)?$request->search:'';

        $data = StockAudit::select('id','product_id','quantity')->with('product:id,name');
        
        if(!empty($search)){
            $data = $data->whereHas('product', function($p) use ($search){
                $p->where('name', 'LIKE','%'.$search.'%');
            } );
        }
        
        $data = $data->where('entry_date',$entry_date)->get()->toArray();

        
        
        $myArr =  array();
        foreach($data as $item){
            
            $myArr[] = array(
                'product_name' => $item['product']['name'],
                'quantity' => $item['quantity']
            );
        }
        // dd($myArr);

        $fileName = "Vozen-System-Stock-".date('Y-m-d',strtotime($entry_date)).".csv";
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Product','Quantity');


        $callback = function() use($myArr, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);            

            foreach ($myArr as $item) {          
                $row['Product']  = $item['product_name'];
                $row['Quantity'] = $item['quantity'];
                                
                fputcsv($file, array($row['Product'], $row['Quantity'] ));                
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
        
    }

    public function store_notes(Request $request)
    {
        # store notes ...
        $paginate = 10;

        $store_id = !empty($request->store_id)?$request->store_id:'';
        $user_id = !empty($request->user_id)?$request->user_id:'';

        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d');
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');

        $store = Store::get();
        $staff = User::where('designation', 1)->get();

        $data = StoreNote::select();

        if(!empty($store_id)){
            $data = $data->where('store_id', $store_id);
        }
        if(!empty($user_id)){
            $data = $data->where('user_id', $user_id);
        }
        
        $data = $data->whereRaw("DATE_FORMAT(created_at,'%Y-%m-%d') BETWEEN '".$from_date."' AND '".$to_date."'  ");

        $data = $data->orderBy('id', 'desc')->paginate($paginate);

        $data = $data->appends([            
            'page' => $request->page,
            'store_id' => $store_id,
            'user_id' => $user_id,
            'from_date' => $from_date,
            'to_date' => $to_date
        ]);

        return view('admin.report.store-notes', compact('data','store_id','user_id','store','staff','from_date','to_date','paginate'));
    }

    public function travel_report(Request $request)
    {
        #  total ...
        $user_id = !empty($request->user_id)?$request->user_id:'';
        $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d');
        $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');


        $data = DB::table('user_attendances AS ua');
        $data = $data->selectRaw("ua.user_id,users.name,SUM(ua.total_distance) AS sum_total_distance");
        // if(empty($user_id)){
        $data = $data->groupBy('user_id');
        // }
        
        $data = $data->leftJoin('users', 'users.id','ua.user_id');
        $data = $data->whereRaw("ua.start_date BETWEEN '".$from_date."' AND '".$to_date."'");

        $data = $data->get();
        // $data = $data->where('user_id', 6)->get();

        // dd($data);


        return view('admin.report.travel', compact('data','user_id','from_date','to_date'));
        
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

                    // dd($opening_bal_date);
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

                    // dd($to_date);

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

        // dd($data);
        
        return view('admin.report.ledger', compact('store','staff','data','user_type','store_id','staff_id','admin_id','supplier_id','select_user_name','from_date','to_date','sort_by','day_opening_amount','is_opening_bal','is_opening_bal_showable','opening_bal_date','bank_cash','non_tr_day_opening_amount','isTransactionFound'));
    }

    public function user_ledger_pdf(Request $request)
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
            $bank_cash = 'bank';
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

    public function user_ledger_csv(Request $request)
{
    $user_type = !empty($request->user_type) ? $request->user_type : '';
    $store_id = !empty($request->store_id) ? $request->store_id : 0;
    $staff_id = !empty($request->staff_id) ? $request->staff_id : 0;
    $admin_id = !empty($request->admin_id) ? $request->admin_id : 0;
    $supplier_id = !empty($request->supplier_id) ? $request->supplier_id : 0;
    $select_user_name = !empty($request->select_user_name) ? $request->select_user_name : '';
    $from_date = !empty($request->from_date) ? $request->from_date : '';
    $to_date = !empty($request->to_date) ? $request->to_date : '';
    $sort_by = !empty($request->sort_by) ? $request->sort_by : 'asc';

    if (Auth::user()->designation == NULL) {
        $bank_cash = !empty($request->bank_cash) ? $request->bank_cash : '';
    } else {
        $bank_cash = 'bank';
    }

    $fileName = ucwords($user_type) . "-" . date('Y-m-d-H-i-s-A') . ".xlsx";

    $day_opening_amount = 0;
    $is_opening_bal = 0;
    $is_opening_bal_showable = 1;
    $opening_bal_date = "";

    $myArr = [];

    if (!empty($user_type)) {

        $data = DB::table('ledger AS l')
            ->select(
                'l.*',
                'p.voucher_no',
                'p.payment_in',
                'p.amount AS payment_amount',
                'p.payment_mode',
                'p.chq_utr_no',
                'p.narration'
            );

        $opening_bal = DB::table('ledger');

        if ($user_type == 'store' && !empty($store_id)) {
            $data = $data->where('l.user_type', 'store')->where('l.store_id', $store_id);
            $opening_bal = $opening_bal->where('user_type', 'store')->where('store_id', $store_id);
        } elseif ($user_type == 'staff' && !empty($staff_id)) {

            $data = $data->where('l.user_type', 'staff')->where('l.staff_id', $staff_id);
            $opening_bal = $opening_bal->where('user_type', 'staff')->where('staff_id', $staff_id);

            $notCommData = DB::table('ledger')
                ->where('user_type', 'staff')
                ->where('staff_id', $staff_id)
                ->whereRaw("(DATE_FORMAT(entry_date, '%Y-%m') < '2023-10' AND purpose = 'payment_collection_commission')")
                ->pluck('id')
                ->toArray();

            if (!empty($notCommData)) {
                $data = $data->whereNotIn('l.id', $notCommData);
                $opening_bal = $opening_bal->whereNotIn('id', $notCommData);
            }

        } elseif ($user_type == 'partner' && !empty($admin_id)) {
            $data = $data->where('l.user_type', 'partner')->where('l.admin_id', $admin_id);
            $opening_bal = $opening_bal->where('user_type', 'partner')->where('admin_id', $admin_id);
        } elseif ($user_type == 'supplier' && !empty($supplier_id)) {
            $data = $data->where('l.user_type', 'supplier')->where('l.supplier_id', $supplier_id);
            $opening_bal = $opening_bal->where('user_type', 'supplier')->where('supplier_id', $supplier_id);
        }

        /* -------- OPENING BALANCE STORE -------- */

        $check_ob_exist_store = DB::table('ledger')
            ->where('purpose', 'opening_balance')
            ->where('user_type', 'store')
            ->where('store_id', $store_id)
            ->orderBy('id', 'asc')
            ->first();

        if (!empty($check_ob_exist_store)) {

            $from_date = ($request->from_date < $check_ob_exist_store->entry_date)
                ? $check_ob_exist_store->entry_date
                : $request->from_date;

            $is_opening_bal = 1;
            $opening_bal_date = $check_ob_exist_store->entry_date;

            if ($from_date == $check_ob_exist_store->entry_date) {
                $is_opening_bal_showable = 0;
            } else {
                $opening_bal = $opening_bal->whereRaw(
                    "entry_date BETWEEN '{$check_ob_exist_store->entry_date}' AND '" .
                    date('Y-m-d', strtotime('-1 day', strtotime($from_date))) . "'"
                );
            }

        } else {
            $opening_bal = $opening_bal->whereRaw(
                "entry_date <= '" . date('Y-m-d', strtotime('-1 day', strtotime($from_date))) . "'"
            );
        }

        /* -------- OPENING BALANCE PARTNER -------- */

        $check_ob_exist_partner = DB::table('ledger')
            ->where('purpose', 'opening_balance')
            ->where('user_type', 'partner')
            ->where('admin_id', $admin_id)
            ->orderBy('id', 'asc')
            ->first();

        if (!empty($check_ob_exist_partner)) {

            $from_date = ($request->from_date < $check_ob_exist_partner->entry_date)
                ? $check_ob_exist_partner->entry_date
                : $request->from_date;

            $is_opening_bal = 1;
            $opening_bal_date = $check_ob_exist_partner->entry_date;

            if ($from_date == $check_ob_exist_partner->entry_date) {
                $is_opening_bal_showable = 0;
            } else {
                $opening_bal = $opening_bal->whereRaw(
                    "entry_date BETWEEN '{$check_ob_exist_partner->entry_date}' AND '" .
                    date('Y-m-d', strtotime('-1 day', strtotime($from_date))) . "'"
                );
            }

        } else {
            $opening_bal = $opening_bal->whereRaw(
                "entry_date <= '" . date('Y-m-d', strtotime('-1 day', strtotime($from_date))) . "'"
            );
        }

        if (!empty($from_date) && !empty($to_date)) {
            $data = $data->whereRaw("l.entry_date BETWEEN '{$from_date}' AND '{$to_date}'");
        }

        if (Auth::user()->type == 2) {
            $opening_bal = $opening_bal->where('is_gst', 1);
        }

        $opening_bal = $opening_bal
            ->orderBy('entry_date', $sort_by)
            ->orderBy('updated_at', $sort_by)
            ->get();

        foreach ($opening_bal as $ob) {
            if (!empty($ob->is_credit)) {
                $day_opening_amount += $ob->transaction_amount;
            }
            if (!empty($ob->is_debit)) {
                $day_opening_amount -= $ob->transaction_amount;
            }
        }

        if (!empty($bank_cash)) {
            $data = $data->where('l.bank_cash', $bank_cash);
        }

        $data = $data->leftJoin('payment AS p', 'p.id', 'l.payment_id')
            ->orderBy('l.entry_date', $sort_by)
            ->orderBy('l.updated_at', $sort_by)
            ->get()
            ->toArray();

        foreach ($data as $item) {
            $myArr[] = [
                'is_credit' => $item->is_credit,
                'is_debit' => $item->is_debit,
                'purpose' => $item->purpose,
                'transaction_id' => $item->transaction_id,
                'transaction_amount' => $item->transaction_amount,
                'entry_date' => $item->entry_date,
                'payment_mode' => $item->payment_mode,
                'bank_cash' => $item->bank_cash
            ];
        }

        if (!empty($is_opening_bal_showable)) {

            $is_credit = $is_debit = 0;
            $getCrDrOB = getCrDr($day_opening_amount);

            if ($getCrDrOB == 'Cr') $is_credit = 1;
            if ($getCrDrOB == 'Dr') $is_debit = 1;

            array_unshift($myArr, [
                'is_credit' => $is_credit,
                'is_debit' => $is_debit,
                'purpose' => 'Opening Balance',
                'transaction_id' => '',
                'transaction_amount' => replaceMinusSign($day_opening_amount),
                'entry_date' => $from_date,
                'payment_mode' => '',
                'bank_cash' => ''
            ]);
        }
    }

    /* ================= EXCEL PREP ================= */

    $excelRows = [];
    $net_value = 0;

    foreach ($myArr as $item) {

        $creditAmt = $debitAmt = '';

        if ($item['is_credit'] == 1) {
            $creditAmt = $item['transaction_amount'];
            $net_value += $item['transaction_amount'];
        }

        if ($item['is_debit'] == 1) {
            $debitAmt = $item['transaction_amount'];
            $net_value -= $item['transaction_amount'];
        }

        $show_payment_mode = !empty($item['bank_cash'])
            ? "( " . ucwords($item['bank_cash']) . " )"
            : "";

        $excelRows[] = [
            'Date' => date('d/m/Y', strtotime($item['entry_date'])),
            'Transaction Id / Voucher No' => $item['transaction_id'],
            'Purpose' => ucwords(str_replace("_", " ", $item['purpose'])) . " " . $show_payment_mode,
            'Debit' => replaceMinusSign($debitAmt),
            'Credit' => $creditAmt,
            'Closing' => replaceMinusSign($net_value) . " " . getCrDr($net_value),
        ];
    }

    return Excel::download(new UserLedgerExport($excelRows), $fileName);
}

    // public function user_ledger_csv(Request $request)
    // {
        
    //     $user_type = !empty($request->user_type)?$request->user_type:'';
    //     $store_id = !empty($request->store_id)?$request->store_id:0;
    //     $staff_id = !empty($request->staff_id)?$request->staff_id:0;
    //     $admin_id = !empty($request->admin_id)?$request->admin_id:0;
    //     $supplier_id = !empty($request->supplier_id)?$request->supplier_id:0;
    //     $select_user_name = !empty($request->select_user_name)?$request->select_user_name:'';
    //     $from_date = !empty($request->from_date)?$request->from_date:'';
    //     $to_date = !empty($request->to_date)?$request->to_date:'';
    //     $sort_by = !empty($request->sort_by)?$request->sort_by:'asc';

    //     if(Auth::user()->designation == NULL){
    //         $bank_cash = !empty($request->bank_cash)?$request->bank_cash:'';
    //     } else {
    //         $bank_cash = 'bank';
    //     }

        

        

    //     $fileName = ucwords($user_type)."-".date('Y-m-d-H-i-s-A').".xlsx";
        
    //     $data = $outstanding = array();
    //     $day_opening_amount = $is_opening_bal =  0;
    //     $is_opening_bal_showable = 1;
    //     $opening_bal_date = "";
        
        
    //     if(!empty($user_type)){
            
    //         DB::enableQueryLog();
    //         $data = DB::table('ledger AS l')->select('l.*','p.voucher_no','p.payment_in','p.amount AS payment_amount','p.payment_mode','p.chq_utr_no','p.narration');
            
    //         $opening_bal = DB::table('ledger');

    //         if($user_type == 'store' && !empty($store_id)){
    //             $data = $data->where('l.user_type', 'store')->where('l.store_id',$store_id);
                
    //             $opening_bal = $opening_bal->where('user_type',$user_type)->where('store_id',$store_id);
    //         }else if($user_type == 'staff'  && !empty($staff_id)){
    //             $data = $data->where('l.user_type', 'staff')->where('l.staff_id',$staff_id);
                
    //             $opening_bal = $opening_bal->where('user_type',$user_type)->where('staff_id',$staff_id);

    //             $notCommData = DB::table('ledger')->where('user_type', 'staff')->where('staff_id',$staff_id)->whereRaw("(DATE_FORMAT(entry_date, '%Y-%m') < '2023-10' AND purpose = 'payment_collection_commission'  )")->pluck('id')->toArray();  

    //             if(!empty($notCommData)){
    //                 // dd($notCommData);
    //                 $data = $data->whereNotIn('l.id',$notCommData);
    //                 $opening_bal = $opening_bal->whereNotIn('id',$notCommData);
    //             }


    //         }else if($user_type == 'partner' && !empty($admin_id)){
    //             $data = $data->where('l.user_type', 'partner')->where('l.admin_id',$admin_id);
                
    //             $opening_bal = $opening_bal->where('user_type',$user_type)->where('admin_id',$admin_id);
    //         }else if($user_type == 'supplier' && !empty($supplier_id)){
    //             $data = $data->where('l.user_type','supplier')->where('l.supplier_id',$supplier_id);
    //             $opening_bal = $opening_bal->where('user_type',$user_type)->where('supplier_id',$supplier_id);
    //         }

    //         $check_ob_exist_store = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'store')->where('store_id',$store_id)->orderBy('id','asc')->first();

    //         if(!empty($check_ob_exist_store)){
    //             $from_date = ($request->from_date < $check_ob_exist_store->entry_date) ? $check_ob_exist_store->entry_date : $request->from_date;
    //             $is_opening_bal = 1;
    //             $opening_bal_date = $check_ob_exist_store->entry_date;

    //             if($from_date == $check_ob_exist_store->entry_date){                    
    //                 $is_opening_bal_showable = 0;                    
    //             } else {
    //                 $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_store->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
    //             }                
                
    //         } else {
    //             // die('Hi');
    //             $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
    //         } 

    //         /* +++++++++++++++++++ */

    //         $check_ob_exist_partner = DB::table('ledger')->where('purpose','opening_balance')->where('user_type', 'partner')->where('admin_id',$admin_id)->orderBy('id','asc')->first();

    //         if(!empty($check_ob_exist_partner)){
    //             $from_date = ($request->from_date < $check_ob_exist_partner->entry_date) ? $check_ob_exist_partner->entry_date : $request->from_date;
    //             $is_opening_bal = 1;
    //             $opening_bal_date = $check_ob_exist_partner->entry_date;

    //             if($from_date == $check_ob_exist_partner->entry_date){                    
    //                 $is_opening_bal_showable = 0;                    
    //             } else {
    //                 $opening_bal = $opening_bal->whereRaw(" entry_date BETWEEN '".$check_ob_exist_partner->entry_date."' AND '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
    //             }                
                
    //         } else {
    //             // die('Hi');
    //             $opening_bal = $opening_bal->whereRaw(" entry_date <= '".date('Y-m-d', strtotime('-1 day', strtotime($from_date)))."'  ");
    //         } 

              
    //         if(!empty($from_date) && !empty($to_date)){
    //             $data = $data->whereRaw("l.entry_date BETWEEN '".$from_date."' AND '".$to_date."' ");
    //         }

    //         if(Auth::user()->type == 2){
    //             $opening_bal = $opening_bal->where('is_gst', 1);
    //         }
            
    //         $opening_bal = $opening_bal->orderBy('entry_date',$sort_by);  
    //         $opening_bal = $opening_bal->orderBy('updated_at',$sort_by);  
    //         $opening_bal = $opening_bal->get();

    //         // dd($opening_bal);

    //         foreach($opening_bal as $ob){
    //             if(!empty($ob->is_credit)){
    //                 $credit_amount = $ob->transaction_amount;
    //                 $day_opening_amount += $ob->transaction_amount;
    //             }
    //             if(!empty($ob->is_debit)){
    //                 $debit_amount = $ob->transaction_amount;
    //                 $day_opening_amount -= $ob->transaction_amount;
    //             }
    //         }

    //         if(!empty($bank_cash)){
    //             $data = $data->where('l.bank_cash', $bank_cash);
    //         }

    //         $data = $data->leftJoin('payment AS p','p.id','l.payment_id');
    //         $data = $data->orderBy('l.entry_date',$sort_by);  
    //         $data = $data->orderBy('l.updated_at',$sort_by);  
    //         $data = $data->get()->toarray(); 

    //         /* ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
            
    //         $myArr = array();
    //         foreach($data  as  $item){
    //             $myArr[] = array(
    //                 'is_credit' => $item->is_credit,
    //                 'is_debit' => $item->is_debit,
    //                 'purpose' => $item->purpose,
    //                 'transaction_id' => $item->transaction_id,
    //                 'transaction_amount' => $item->transaction_amount,
    //                 'entry_date' => $item->entry_date,
    //                 'payment_mode' => $item->payment_mode,
    //                 'bank_cash' => $item->bank_cash
    //             ); 
                
    //         }

            
            
            
    //         if(!empty($is_opening_bal_showable)){
    //             $is_credit = $is_debit = 0;
    //             $getCrDrOB = getCrDr($day_opening_amount);
    //             if($getCrDrOB == 'Cr'){
    //                 $is_credit = 1;
    //             } else if($getCrDrOB == 'Dr'){
    //                 $is_debit = 1;
    //             } else if($getCrDrOB == ''){
                    
    //             }
    //             $ob_arr = array(
    //                 'is_credit' => $is_credit,
    //                 'is_debit' => $is_debit,
    //                 'purpose' => "Opening Balance",
    //                 'transaction_id' => '',
    //                 'transaction_amount' => replaceMinusSign($day_opening_amount),
    //                 'entry_date' => $from_date,
    //                 'payment_mode' => '',
    //                 'bank_cash' => ''
    //             );

    //             array_unshift($myArr,$ob_arr);
                
    //         }
    //         // echo '<pre>'; print_r($myArr);
    //         // echo '<pre>'; print_r($data);
    //     }

    //     // die;

    //     $headers = array(
    //         "Content-type"        => "text/csv",
    //         "Content-Disposition" => "attachment; filename=$fileName",
    //         "Pragma"              => "no-cache",
    //         "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
    //         "Expires"             => "0"
    //     );

    //     $columns = array('Date','Transaction Id / Voucher No', 'Purpose', 'Debit', 'Credit',  'Closing');

    //     $callback = function() use($myArr, $columns) {
    //         $file = fopen('php://output', 'w');
    //         fputcsv($file, $columns);
    //         $net_value = 0;

    //         foreach ($myArr as $item) {
    //             $creditAmt = $debitAmt = '';
    //             if($item['is_credit'] == 1){
    //                 $creditAmt = $item['transaction_amount'];
    //                 $net_value += $item['transaction_amount'];
    //             }
    //             if($item['is_debit'] == 1){
    //                 $debitAmt = ($item['transaction_amount']);
    //                 $net_value -= $item['transaction_amount'];
    //             }
    //             // echo $net_value; die;
                
    //             $show_payment_mode = !empty($item['bank_cash']) ? "( ".ucwords($item['bank_cash'])." )" : "";
    //             $row['Date']  = date('d/m/Y', strtotime($item['entry_date']));
    //             $row['Transaction Id / Voucher No'] = $item['transaction_id'];
    //             $row['Purpose'] = ucwords(str_replace("_"," ",$item['purpose']))." ".$show_payment_mode;                
    //             $row['Debit']  = replaceMinusSign($debitAmt);
    //             $row['Credit']    = $creditAmt;
    //             $row['Closing']  =  replaceMinusSign($net_value)." ".getCrDr($net_value);

    //             fputcsv($file, array($row['Date'], $row['Transaction Id / Voucher No'],$row['Purpose'], $row['Debit'], $row['Credit'], $row['Closing']));                
    //         }
    //         fclose($file);
    //     };
    //     return response()->stream($callback, 200, $headers);
    // }

    public function barcode_history(Request $request)
    {
        $barcode_no = !empty($request->barcode_no)?$request->barcode_no:'';
        $isPurchaseOrder = $isPurchaseReturn = $isSalesOut = $isStoreReturn = $isGrn = $isItemFound = false;
        $packingslip = $return_product = $product = $purchase_order_box = $return_boxes = $stock_boxes = $purchase_return_box = array();

        if(!empty($barcode_no)){
            $purchase_order_box = PurchaseOrderBox::where('barcode_no',$barcode_no)->first();
            if(!empty($purchase_order_box)){
                $product_id = $purchase_order_box->product_id;
                $product = Product::find($product_id);
                $isItemFound = true;

                $purchase_order_product = PurchaseOrderProduct::where('purchase_order_id',$purchase_order_box->purchase_order_id)->where('product_id',$purchase_order_box->product_id)->first();
                $piece_price = $purchase_order_product->piece_price;
                $purchase_order_box->piece_price = $piece_price;
            }
            $return_boxes = ReturnBox::where('barcode_no',$barcode_no)->first();
            if(!empty($return_boxes)){
                $product_id = $return_boxes->product_id;
                $product = Product::find($product_id);
                $isItemFound = true;

                $return_product = ReturnProduct::where('return_id',$return_boxes->return_id)->where('product_id',$return_boxes->product_id)->first();
                $piece_price = $return_product->piece_price;
                $return_product->piece_price = $piece_price;

            }
            $stock_boxes = StockBox::where('barcode_no',$barcode_no)->first();
            if(!empty($stock_boxes)){
                $stock_product = StockProduct::where('stock_id',$stock_boxes->stock_id)->where('product_id',$stock_boxes->product_id)->first();
                $piece_price = $stock_product->piece_price;
                $stock_boxes->piece_price = $piece_price;
                $packingslip_id = $stock_boxes->packingslip_id;
                
                if(!empty($packingslip_id)){
                    $packingslip = PackingslipNew1::find($packingslip_id);
                    $order_id = $packingslip->order_id;
                    $order_product = OrderProduct::where('order_id',$order_id)->where('product_id',$stock_boxes->product_id)->first();
                    $packingslip->order_product = $order_product;
                    
                }

                $purchase_return_id = $stock_boxes->purchase_return_id;
                if(!empty($purchase_return_id)){
                    $purchase_return_product = PurchaseReturnProduct::where('return_id',$purchase_return_id)->where('product_id', $stock_boxes->product_id)->first();
                    $stock_boxes->purchase_return_date = $purchase_return_product->created_at;

                    $purchase_return_box = PurchaseReturnBox::where('barcode_no',$barcode_no)->where('return_id',$purchase_return_id)->first();

                    // dd($purchase_return_box);

                }
                
            }
            
            
            if(!empty($purchase_order_box)){
                $isPurchaseOrder = true;
            }
            if(!empty($return_boxes)){
                $isStoreReturn = true;
            }
            if(!empty($stock_boxes)){
                $isGrn = true;
            }
            if(!empty($stock_boxes) && !empty($stock_boxes->packingslip_id)){
                $isSalesOut = true;
            }
            if(!empty($stock_boxes) && !empty($stock_boxes->purchase_return_id)){
                $isPurchaseReturn = true;
            }
        }

        return view('admin.report.barcode-history', compact(
                                                            'barcode_no',
                                                            'isPurchaseOrder',
                                                            'isPurchaseReturn',
                                                            'isSalesOut',
                                                            'isStoreReturn',
                                                            'isGrn',
                                                            'isItemFound',
                                                            'product',
                                                            'purchase_order_box',
                                                            'return_boxes',
                                                            'stock_boxes',
                                                            'packingslip',
                                                            'return_product',
                                                            'purchase_return_box'
                                                        )
                                                    );

        
        


    }

    public function stock_log(Request $request)
    {
        $entry_date = !empty($request->entry_date)?$request->entry_date:date('Y-m-d');
        $product_id = !empty($request->product_id)?$request->product_id:'';
        $product_name = !empty($request->product_name)?$request->product_name:'';
        $type = !empty($request->type)?$request->type:'';
        $data = StockLog::where('entry_date', $entry_date);
        if(!empty($product_id)){
            $data = $data->where('product_id',$product_id);
        }
        if(!empty($type)){
            $data = $data->where('type',$type);
        }
        $data = $data->orderBy('entry_date')->orderBy('created_at')->get();

        // dd($data);


        return view('admin.report.stock-logs', compact('entry_date','data','product_name','product_id','type'));
    }

    public function stock_log_csv(Request $request)
    {
        $entry_date = !empty($request->entry_date)?$request->entry_date:date('Y-m-d');
        $product_id = !empty($request->product_id)?$request->product_id:'';
        $product_name = !empty($request->product_name)?$request->product_name:'';
        $data = StockLog::where('entry_date', $entry_date);
        if(!empty($product_id)){
            $data = $data->where('product_id',$product_id);
        }
        $data = $data->orderBy('entry_date')->orderBy('created_at')->get();


        $myArr = array();        
        foreach($data as $item){
            $purpose = $particular = $userName = "";
            $in_quantity = $out_quantity = '';
            if($item->type == 'in'){
                $purpose = "GOODS RECEIVED";
                $particular = "GRN / ".$item->stock->grn_no;
                if(!empty($item->stock->purchase_order->supplier)){
                    $userName = 'SUPPLIER:- '.$item->stock->purchase_order->supplier->name;
                } else {
                    $userName = 'STORE:- '.$item->stock->returns->store->bussiness_name;
                }
            } 
            if($item->type == 'out'){
                $purpose = "GOODS DISBURSED";
                if(!empty($item->packingslip_id)){
                    $particular = "PACKING SLIP / ".$item->packingslip->slipno;
                    $particular = "PACKING SLIP / ".$item->packingslip->slipno;
                    $userName = 'STORE:- '.$item->packingslip->store->bussiness_name;
                }
                if(!empty($item->purchase_return_id)){
                    $particular = "PURCHASE RETURN / ".$item->purchase_return->order_no;
                    $particular = "PURCHASE RETURN / ".$item->purchase_return->order_no;
                    $userName = 'SUPPLIER:- '.$item->purchase_return->supplier->name;
                }
                
            }
            $myArr[] = array(
                'product' => $item->product->name,
                'purpose' => $purpose,
                'particular' => $particular,
                'userName' => $userName,
                'piece_price' => $item->piece_price,
                'quantity' => $item->quantity
            ); 
        }

        
        // dd($myArr);
        $fileName = "vozen-daily-stocklogs-".date('Ymd',strtotime($entry_date)).".csv";
        // dd($fileName);
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('#','Product','Purpose','Paticular','Rate','Quantity (Ctns)');

        // dd($myArr);

        $callback = function() use($myArr, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            $net_quantity = 0; 
            $i=1;
            foreach ($myArr as $item) {   
                
                $row['#'] = $i;
                $row['Product'] = $item['product'];
                $row['Purpose'] = $item['purpose'];
                $row['Paticular'] = $item['userName'];
                $row['Rate'] = !empty($item['piece_price'])?'XOF. '.number_format((float)$item['piece_price'], 2, '.', ''):'';
                $row['Quantity (Ctns)'] = $item['quantity'];
                                
                fputcsv($file, array($row['#'], $row['Product'], $row['Purpose'], $row['Paticular'],$row['Rate'], $row['Quantity (Ctns)']));    
                
                $i++;
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);


    }



   public function attendance_report(Request $request)
{
    $month = $request->month ?? now()->month;
    $year  = $request->year ?? now()->year;

    $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

    // Fetch all employees
    $users = User::where('type', 2)->get();

    // Preload all attendances for the month once
    $attendances = UserAttendance::whereMonth('start_date', $month)
        ->whereYear('start_date', $year)
        ->get()
        ->groupBy([
            'user_id',
            fn ($item) => Carbon::parse($item->start_date)->day
        ]);

    $attendanceSheet = [];

    foreach ($users as $user) {
        $dailyStatus = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day)->toDateString();

            if (isset($attendances[$user->id][$day][0])) {
                // If attendance exists → Present
                $status = 'P';
            } elseif (Carbon::parse($date)->isWeekend()) {
                $status = 'W';
            } else {
                // No attendance → Absent
                $status = '-';
            }

            $dailyStatus[$day] = $status;
        }

        $attendanceSheet[] = [
            'user' => $user,
            'attendance' => $dailyStatus
        ];
    }
    // dd($attendanceSheet);

    return view('admin.employee_attendance.index', compact(
        'attendanceSheet',
        'month',
        'year',
        'daysInMonth'
    ));
}




}
