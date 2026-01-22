<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use File; 
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\StockAudit;
use App\Models\StockBarcodeUpload;
use App\Models\StockAuditFinal;
use App\Models\Product;

class StockAuditController extends Controller
{
    public function __construct(Request $request)
    {
        # code...
    }

    public function index(Request $request)
    {
        # code...
        $paginate = 20;
        $data = StockAudit::orderBy('id','desc')->groupBy(DB::raw("DATE_FORMAT(created_at,'%Y-%m-%d')"))->paginate($paginate);
        // dd($data);
        return view('admin.stockaudit.list', compact('data'));
    }

    public function upload_csv(Request $request)
    {
        # code...

        $request->validate([
            'csv' => 'required'
        ]);
        $params = $request->except('_token');
        // dd($params);
        $csv = $params['csv'];                
        StockBarcodeUpload::where('entry_date',$params['entry_date'])->delete();        
        $rows = Excel::toArray([],$request->file('csv'));
        $data = $rows[0];
        // dd($data);
        foreach($data as $item){
            $org_barcode_no = $item[0];
            $barcode_no = trim($org_barcode_no);
            
            // echo 'barcode_no:- '.$barcode_no.'<br/>'; 
            $getBarcodeDetailsStock = getBarcodeDetailsStock($barcode_no);
            if(empty($getBarcodeDetailsStock)){                
                $erroMsg = "Mismatched or Unknown Barcode -- '".$org_barcode_no."' , Please Check";
                Session::flash('messageErr', $erroMsg);
                return redirect()->route('admin.stockaudit.list');  
            }

            $product_id = $getBarcodeDetailsStock['product_id'];
            
            StockBarcodeUpload::insert([
                'product_id' => $product_id,
                'barcode_no' => $barcode_no,
                'entry_date' => $params['entry_date'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->setQuantity($params['entry_date']);
        
        $successMsg = "Godown Stock Uploaded Successfully for ".date('d/m/Y', strtotime($params['entry_date']));
        Session::flash('message', $successMsg);
        return redirect()->route('admin.stockaudit.list');       


    }

    private function setQuantity($entry_date)
    {
        # Set quantity...

        StockAuditFinal::where('entry_date', $entry_date)->delete();

        $data = StockBarcodeUpload::select('product_id')->selectRaw("COUNT(barcode_no) AS quantity")->with('product:id,name')->where('entry_date',$entry_date)->groupBy('product_id')->get()->toArray();

        if(!empty($data)){
            $uploaded_proids = array();
            foreach($data as $item){
                $uploaded_proids[] = $item['product_id'];
            }

            // $all_product = Product::select('id')->with('count_stock')->orderBy('id')->get()->toArray();
            $all_product = StockAudit::where('entry_date', $entry_date)->get()->toArray();
            
            $allProIds = array();
            foreach($all_product as $pro){
                if(!in_array($pro['product_id'],$uploaded_proids)){
                    $not_uploaed_pro_system_stock = $pro['quantity'];
                    $not_uploaed_pro_godown_stock = 0;
                    $not_uploaed_pro_stock_status = 'matched';
                    if($not_uploaed_pro_system_stock > $not_uploaed_pro_godown_stock){
                        $not_uploaed_pro_stock_status = 'excess_system';
                    } else if ($not_uploaed_pro_system_stock < $not_uploaed_pro_godown_stock){
                        $not_uploaed_pro_stock_status = 'excess_godown';
                    }
                    StockAuditFinal::insert([
                        'entry_date' => $entry_date,
                        'product_id' => $pro['product_id'],
                        'system_quantity' => $not_uploaed_pro_system_stock,
                        'godown_quantity' => $not_uploaed_pro_godown_stock,
                        'stock_status' => $not_uploaed_pro_stock_status,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                }
            }
            
            foreach($data as $item){
                $system_stock = StockAudit::where('entry_date', $entry_date)->where('product_id',$item['product_id'])->first();

                $system_quantity = !empty($system_stock)?$system_stock->quantity:0;
                
                $stock_status = 'matched';
                if($system_quantity > $item['quantity']){
                    $stock_status = 'excess_system';
                } else if ($system_quantity < $item['quantity']){
                    $stock_status = 'excess_godown';
                }

                StockAuditFinal::insert([
                    'entry_date' => $entry_date,
                    'product_id' => $item['product_id'],
                    'system_quantity' => $system_quantity,
                    'godown_quantity' => $item['quantity'],
                    'stock_status' => $stock_status,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            }
        }


    }

    public function get_uploaded_log_csv(Request $request)
    {
        # Get Uploaded Log CSV...
        $entry_date = $request->entry_date;

        $data = StockBarcodeUpload::select('product_id')->selectRaw("COUNT(barcode_no) AS quantity")->with('product:id,name')->where('entry_date',$entry_date)->groupBy('product_id')->get()->toArray();

        $myArr = array();
        if(!empty($data)){
            foreach($data as $item){
                $myArr[] = array(
                    'product_name' => $item['product']['name'],
                    'quantity' => $item['quantity']
                );
            }
        }
        
        // dd($myArr);


        $fileName = "WMTOOLS-Godown-Stock-".date('Y-m-d',strtotime($entry_date)).".csv";
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

            if(!empty($myArr)){
                foreach ($myArr as $item) {          
                    $row['Product']  = $item['product_name'];
                    $row['Quantity'] = $item['quantity'];
                                    
                    fputcsv($file, array($row['Product'], $row['Quantity'] ));                
                }
            } 
            
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);

    }

    public function view_final_stock(Request $request,$entry_date)
    {
        # View Final Stock...
        $paginate = 20;
        $search = !empty($request->search)?$request->search:'';
        $stock_status = !empty($request->stock_status)?$request->stock_status:'';
        $data = StockAuditFinal::where('entry_date',$entry_date);
        $countData = StockAuditFinal::where('entry_date',$entry_date);
        
        if(!empty($search)){
            $data = $data->whereHas('product', function($q) use ($search){
                $q->where('name', 'LIKE', '%'.$search.'%');
            });
            $countData = $countData->whereHas('product', function($q) use ($search){
                $q->where('name', 'LIKE', '%'.$search.'%');
            });
        }

        if(!empty($stock_status)){
            $data = $data->where('stock_status',$stock_status);
            $countData = $countData->where('stock_status',$stock_status);
        }

        $data = $data->paginate($paginate);
        $countData = $countData->count();

        $data->appends([
            'search' => $search,
            'stock_status' => $stock_status,
            'page' => $request->page
        ]);

        return view('admin.stockaudit.view-stock', compact('data','countData','entry_date','search','stock_status','paginate'));
    }
}
