<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Returns;
use App\Models\ReturnProduct;
use App\Models\ReturnBox;
use App\Models\Store;
use App\Models\Stock;
use App\Models\StockProduct;
use App\Models\StockBox;
use App\Models\StockLog;
use App\Models\Ledger;
use App\Models\Payment;
use App\Models\InvoiceProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class ReturnItemController extends Controller
{
    //

    public function index(Request $request)
    {
        $search = !empty($request->search)?$request->search:'';
        $paginate = 20;
        $data = Returns::with('store:id,store_name,bussiness_name');
        $totalData = Returns::with('store:id,store_name,bussiness_name');
        
        if(!empty($search)){
            $data = $data->whereHas('store', function($s) use ($search){
                $s->where('store_name', 'LIKE', '%'.$search.'%')->orWhere('bussiness_name', 'LIKE', '%'.$search.'%');
            });
            $totalData = $totalData->whereHas('store', function($s) use ($search){
                $s->where('store_name', 'LIKE', '%'.$search.'%')->orWhere('bussiness_name', 'LIKE', '%'.$search.'%');
            });
        }

        $data = $data->orderBy('id','desc')->paginate($paginate);
        $totalData = $totalData->count();

        $data = $data->appends([
            'page' => $request->page,
            'search' => $search
        ]);

        // dd($data);

        return view('admin.returns.index', compact('data','totalData','search','paginate'));
    }

    public function details(Request $request,$id)
    {
        $data = Returns::find($id);
        return view('admin.returns.detail', compact('id','data'));
    }

    public function add(Request $request)
    {
        $store = Store::select('id','store_name','bussiness_name')->orderBy('bussiness_name','asc')->get();
        return view('admin.returns.add', compact('store'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'details.*.product_id' => 'required',            
            'details.*.hsn_code' => 'required',
            'details.*.pcs' => 'required|not_in:0',
            'details.*.piece_price' => 'required|not_in:0',
            'details.*.qty' => 'required|not_in:0'            
        ],[
            'details.*.product_id.required' => 'Please add product',
            'details.*.hsn_code.required' => 'Please add hsn code of product',
            'details.*.pcs.required' => 'Please add pices per carton',
            'details.*.pcs.not_in' => 'Please add pices per carton',
            'details.*.piece_price.required' => 'Please add price per piece',
            'details.*.piece_price.not_in' => 'Please add price per piece',
            'details.*.qty.required' => 'Please add number of carton',
            'details.*.qty.not_in' => 'Please add number of carton'
        ]);

        $params = $request->except('_token');
        $store_id = $params['store_id'];
        // dd($params);
        $details = $params['details'];
        
        $return_price = 0;
        $isItemErrorTrue = false;
        $itemErrorText = "";
        $itemErrMsg = array();
        foreach($details  as $key => $item){
            ## Checck store ordered the product previously or not ...
            $checkStoreProduct = InvoiceProduct::where('product_id', $item['product_id'])->whereHas('invoice', function($invoice) use ($store_id){
                $invoice->where('store_id', $store_id);
            })->first();

            

            // dd($checkStoreProduct);

            if(empty($checkStoreProduct)){
                // dd('Hi');
                $isItemErrorTrue = true;
                $itemErrorText .= "".$item['product']." , ";
                $itemErrMsg = array_merge($itemErrMsg,['details.'.$key.'.product_id'=> "This item is not sold for this store"]);
            }

            $return_price += $item['total_price'];
        }

        if($isItemErrorTrue){
            // dd('Hi');
            return  redirect()->back()->withErrors($itemErrMsg)->withInput();
        }
        // dd($details);
        // dd($details);
       
        $return_data = array(
            'order_no' => $params['order_no'],
            'store_id' => $params['store_id'],   
            'amount' => $return_price,   
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')               
        );
        $return_id = Returns::insertGetId($return_data);

        foreach($details as $item){

            $retProArr = array(
                'return_id' => $return_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['qty'],
                'pcs' => $item['pcs'],
                'hsn_code' => $item['hsn_code'],
                'unit_price' => $item['price_per_carton'],
                'piece_price' => $item['piece_price'],
                'total_price' => $item['total_price'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );            
            ReturnProduct::insert($retProArr);

            

            for($j=0;$j < ($item['qty']); $j++){
                $barcodeGen = genAutoIncreNoBarcodeReturn($item['product_id'],date('Y'));
                $barcode_no = $barcodeGen['barcode_no'];
                $code_html = $barcodeGen['code_html'];
                $code_base64_img = $barcodeGen['code_base64_img'];

                $retBoxArr = array(
                    'return_id' => $return_id,
                    'product_id' => $item['product_id'],
                    'pcs' => $item['pcs'],
                    'barcode_no' => $barcode_no,
                    'code_html' => $code_html,
                    'code_base64_img' => $code_base64_img,
                    'created_at' => date('Y-m-d H:i:s')
                );
                ReturnBox::insert($retBoxArr);
            }

        }

        Session::flash('message', 'Return order created successfully');
        return redirect()->route('admin.returns.list');
    }

    public function edit(Request $request,$id)
    {
        
        $data = Returns::with('return_products')->with('store:id,store_name,bussiness_name')->find($id);
        return view('admin.returns.edit', compact('id','data'));
    }

    public function update(Request $request,$id)
    {
        $request->validate([
            'details.*.product_id' => 'required',            
            'details.*.hsn_code' => 'required',
            'details.*.pcs' => 'required|not_in:0',
            'details.*.piece_price' => 'required|not_in:0',
            'details.*.qty' => 'required|not_in:0'            
        ],[
            'details.*.product_id.required' => 'Please add product',
            'details.*.hsn_code.required' => 'Please add hsn code of product',
            'details.*.pcs.required' => 'Please add pices per carton',
            'details.*.pcs.not_in' => 'Please add pices per carton',
            'details.*.piece_price.required' => 'Please add price per piece',
            'details.*.piece_price.not_in' => 'Please add price per piece',
            'details.*.qty.required' => 'Please add number of carton',
            'details.*.qty.not_in' => 'Please add number of carton'
        ]);
        $params = $request->except('_token');
        // dd($params);

        $details = $params['details'];

        $oldProIds = $currentProIds = $removeProIdArr = array();
        $old_return_products = ReturnProduct::select('product_id')->where('return_id',$id)->get();
        foreach($old_return_products as $pro){
            $oldProIds[] = $pro->product_id;
        }
        foreach($details as $newItem){
            $currentProIds[] = $newItem['product_id'];            
        }
        foreach($oldProIds as $value){
            if(!in_array($value,$currentProIds)){
                $removeProIdArr[] = $value;
            }
        }
        // echo '<pre>oldProIds:- '; print_r($oldProIds);
        // echo '<pre>currentProIds:- '; print_r($currentProIds);
        // echo '<pre>removeProIdArr:- '; print_r($removeProIdArr);
        
        #1: Initially all boxes make them unscanned
        ReturnBox::where('return_id',$id)->update([
            'is_scanned' => 0,
            'is_bulk_scanned' => 0, 
            'is_goods_in' => 0
        ]);
        if(!empty($removeProIdArr)){
            foreach($removeProIdArr as $value){
                #1: Delete Return Product
                ReturnProduct::where('return_id',$id)->where('product_id',$value)->delete();
                #2: Delete Return Box
                ReturnBox::where('return_id',$id)->where('product_id',$value)->delete();
            }
        }

        foreach($details as $item){
            if($item['isOld'] == 0){
                #1: Insert Return Product 
                $addProductData = array(
                    'return_id' => $id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['qty'],
                    'pcs' => $item['qty'],
                    'hsn_code' => $item['hsn_code'],
                    'piece_price' => $item['piece_price'],
                    'unit_price' => $item['price_per_carton'],
                    'total_price' => $item['total_price'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                // echo '<pre>addProductData:- '; print_r($addProductData);
                ReturnProduct::insert($addProductData);
                for($j=0;$j < ($item['qty']); $j++){
                    
                    $barcodeGen = genAutoIncreNoBarcodeReturn($item['product_id'],date('Y'));
                    $barcode_no = $barcodeGen['barcode_no'];
                    $code_html = $barcodeGen['code_html'];
                    $code_base64_img = $barcodeGen['code_base64_img'];
                    $addBoxData = array(
                        'return_id' => $id,
                        'product_id' => $item['product_id'],
                        'pcs' => $item['pcs'],
                        'barcode_no' => $barcode_no,
                        'code_html' => $code_html,
                        'code_base64_img' => $code_base64_img,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    );
                    // echo '<pre>addBoxData:- '; print_r($addBoxData);
                    #2: Insert Return Box
                    ReturnBox::insert($addBoxData);
                    
                }
                
            }
            if($item['isOld'] == 1){
                if($item['isNoCtnChanged'] == 1){

                    if($item['qty'] > $item['oldQty']){
                        $rest = ($item['qty'] - $item['oldQty']);
                        // echo 'product_id:- '.$item['product_id'].' Add New:- '.$rest;
                        // echo '<br/>';
                        for($j=0;$j < ($rest); $j++){
                            $barcodeGen1 = genAutoIncreNoBarcodeReturn($item['product_id'],date('Y'));
                            $barcode_no1 = $barcodeGen1['barcode_no'];
                            $code_html1 = $barcodeGen1['code_html'];
                            $code_base64_img1 = $barcodeGen1['code_base64_img'];
                            ReturnBox::insert([
                                'return_id' => $id,
                                'product_id' => $item['product_id'],
                                'pcs' => $item['pcs'],
                                'barcode_no' => $barcode_no1,
                                'code_html' => $code_html1,
                                'code_base64_img' => $code_base64_img1,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        }
    
                    } else {
                        $rest = ($item['oldQty'] - $item['qty']);
                        // echo 'product_id:- '.$item['product_id'].' Delete Old:- '.$rest;
                        // echo '<br/>';
                        $old_rest_box = ReturnBox::where('return_id',$id)->where('product_id',$item['product_id'])->orderBy('id','desc')->take($rest)->delete();
    
                    }

                }
                $editProductData = array(
                    'quantity' => $item['qty'],
                    'pcs' => $item['pcs'],
                    'hsn_code' => $item['hsn_code'],
                    'piece_price' => $item['piece_price'],
                    'unit_price' => $item['price_per_carton'],
                    'total_price' => $item['total_price'],
                    'updated_at' => date('Y-m-d H:i:s')
                );
                ReturnProduct::where('return_id',$id)->where('product_id',$item['product_id'])->update($editProductData);
            }

        }
        Returns::where('id',$id)->update([
            'amount' => $params['amount'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        // die;
        Session::flash('message', 'Return order updated successfully');
        return redirect()->route('admin.returns.list');
    }

    public function edit_amount(Request $request,$id)
    {
        $data = Returns::with('return_products')->with('store:id,store_name,bussiness_name')->find($id);
        return view('admin.returns.edit_amount', compact('data','id'));
    }

    public function update_amount(Request $request,$id)
    {
        $request->validate([
            'details.*.pcs' => 'required|not_in:0',
            'details.*.piece_price' => 'required|not_in:0'
        ],[
            'details.*.pcs.required' => 'Please add pices per carton',
            'details.*.pcs.not_in' => 'Please add pices per carton',
            'details.*.piece_price.required' => 'Please add price per piece',
            'details.*.piece_price.not_in' => 'Please add price per piece'
        ]);
        
        $params = $request->except('_token');
        // dd($params);

        Returns::where('id',$id)->update([
            'amount' => $params['amount'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $details = $params['details'];

        $stock = Stock::where('return_id',$id)->first();
        $stock_id = $stock->id;
        Stock::where('id',$stock_id)->update([
            'total_price' => $params['amount']
        ]);
        $grn_no = $stock->grn_no;

        foreach($details as $item){
            #1: Return Product pricing update
            ReturnProduct::where('return_id',$id)->where('product_id',$item['product_id'])->update([
                'pcs' => $item['pcs'],
                'piece_price' => $item['piece_price'],
                'unit_price' => $item['price_per_carton'],
                'total_price' => $item['total_price'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            #2: Stock Product pricing update
            StockProduct::where('stock_id',$stock_id)->where('product_id',$item['product_id'])->update([
                'unit_price' => $item['price_per_carton'],
                'piece_price' => $item['piece_price'],
                'total_price' => $item['total_price']
            ]);
            #3: Stock Log Update
            StockLog::where('stock_id',$stock_id)->where('product_id',$item['product_id'])->update([                
                'quantity' => $item['count_scanned'],
                'piece_price' => $item['piece_price'],
                'carton_price' => $item['price_per_carton'],
                'total_price' => $item['total_price'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $returns = Returns::find($id);
        $ledger_id = $returns->ledger_id;
        $order_no = $returns->order_no;

        #3: Ledger amount update
        Ledger::where('id',$ledger_id)->update([
            'transaction_amount' => $params['amount'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        #4: Journal amount update
        DB::table('journal')->where('purpose_id',$grn_no)->update([
            'transaction_amount' => $params['amount'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        Session::flash('message', 'Return order amount updated successfully');
        return redirect()->route('admin.returns.list');
    }

    public function barcode(Request $request,$id)
    {
        $data = ReturnBox::with('product:id,name')->where('return_id',$id)->orderBy('product_id')->get();
        // dd($data);
        return view('admin.returns.barcode', compact('data','id'));
    }

    // Ajax    
    public function goods_in(Request $request,$id)
    {
        $goods_in_type = !empty($request->goods_in_type)?$request->goods_in_type:'';
        $search = !empty($request->search)?$request->search:'';
        $returns = Returns::find($id);
        $data = ReturnBox::with('product')->where('return_id',$id)->where('is_goods_in', 0);        
        if(!empty($search)){
            $data = $data->where(function($q) use ($search){
                $q->where('barcode_no','LIKE', '%'.$search.'%')->orWhereHas('product', function ($product) use ($search) {
                    $product->where('name', 'LIKE','%'.$search.'%');
                });
            });
        }
        $data = $data->orderBy('id','asc')->get()->groupBy('product.id');  

        if(!empty($goods_in_type)){
            Returns::where('id',$id)->update(['goods_in_type'=>$goods_in_type]);
        }

        $total_checked = ReturnBox::where('return_id',$id)->where(function($q){
            $q->where('is_scanned', 1)->orWhere('is_bulk_scanned', 1);
        })->count();

        $total_checkbox = ReturnProduct::where('return_id',$id)->sum('quantity');

        // dd($data);

        return view('admin.returns.goodsin', compact('returns','data','id','total_checked','total_checkbox','goods_in_type','search'));
    }

    public function returnbulkscan(Request $request)
    {
        # RETURN Bulk Scan...

        // dd($request->all());

        $return_id = !empty($request->return_id)?$request->return_id:'';
        $product_id = !empty($request->product_id)?$request->product_id:'';
        $is_bulk_scanned = $request->is_bulk_scanned;
        $is_scanned = $request->is_scanned;
        $data = ReturnBox::where('return_id',$return_id)->where('product_id',$product_id)->where(function($q){
            $q->where('is_scanned', 0)->orWhere('is_bulk_scanned', 0);
        })->get();

        if(!empty($data)){
            foreach($data as $item){
                ReturnBox::where('id',$item->id)->update([
                    'is_bulk_scanned'=>$is_bulk_scanned,
                    // 'is_goods_in'=>$is_scanned
                ]);
            }
        }

        return 1;
    }

    public function save_goods_in(Request $request)
    {
        $request->validate([
            'barcode_no' => 'required'
        ],[
            'barcode_no.required'=>'Please scan at least one barcode'
        ]);

        $params = $request->except('_token');
        $grn_no = getRandString(16);
        // dd($params);

        $stock_id = Stock::insertGetId([
            'return_id' => $params['id'],
            'return_order_no' => $params['return_order_no'],
            'goods_in_type' => $params['goods_in_type'],
            'grn_no' => $grn_no,
            'created_at'=>date('Y-m-d H:i:s')
            // 'product_ids' => $product_ids,    
        ]);

        $products = $params['products'];
        $proids = array();
        $product_ids = '';
        foreach($products as $item){
            if(!empty($item['count_scanned'])){
                $proids[] = $item['product_id'];
                StockProduct::insert([
                    'stock_id' => $stock_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['count_scanned'],
                    'unit_price' => $item['unit_price'],
                    'piece_price' => $item['piece_price'],
                    'total_price' => ($item['count_scanned'] * $item['unit_price']),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $product_ids = implode(",",$proids);
                # Entry Stock Log
                StockLog::insert([
                    'product_id' => $item['product_id'],
                    'entry_date' => date('Y-m-d'),
                    'stock_id' => $stock_id,
                    'quantity' => $item['count_scanned'],
                    'piece_price' => $item['piece_price'],
                    'carton_price' => $item['unit_price'],
                    'total_price' => ($item['count_scanned'] * $item['unit_price']),
                    'type' => 'in',
                    'entry_type' => 'grn',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
        }

        $total_stock_price = StockProduct::where('stock_id',$stock_id)->sum('total_price');
        Stock::where('id',$stock_id)->update([
            'product_ids' => $product_ids,
            'total_price' => $total_stock_price
        ]);
        // die;
        $barcode_no = $params['barcode_no'];
        ReturnBox::whereIn('barcode_no',$barcode_no)->update(['is_goods_in' => 1]);

        
        foreach($barcode_no as $barcode){
            $getBarcodeDetailsReturns = getBarcodeDetailsReturns($barcode);
            StockBox::insert([
                'stock_id' => $stock_id,
                'product_id' => $getBarcodeDetailsReturns['product_id'],
                'pcs' => $getBarcodeDetailsReturns['pcs'],
                'barcode_no' => $barcode,
                'code_html' => $getBarcodeDetailsReturns['code_html'],
                'code_base64_img' => $getBarcodeDetailsReturns['code_base64_img'],
                'created_at'=>date('Y-m-d H:i:s')
            ]);
        }

        $total_box = ReturnBox::where('return_id',$params['id'])->count();
        $total_scanned_box = ReturnBox::where('return_id',$params['id'])->where('is_goods_in', 1)->count();

        if($total_box == $total_scanned_box){
            Returns::where('id',$params['id'])->update([
                'is_goods_in' => 1
            ]);
        }

        /* payemtn & ledger entry  */
        $returns = Returns::find($params['id']);
        $store_id = $returns->store_id;
        $amount = $returns->amount;

        $ledger_id = Ledger::insertGetId([
            'user_type' => 'store',
            'store_id' => $store_id,
            'is_credit' => 1,
            'transaction_id' => $grn_no,
            'transaction_amount' => $amount,    
            'entry_date' => date('Y-m-d'),
            'purpose' => 'goods_return_slip',
            'purpose_description' => 'Store Return Items',
            'created_at'=>date('Y-m-d H:i:s')
        ]);

        Returns::where('id', $params['id'])->update([
            'ledger_id' => $ledger_id
        ]);

        DB::table('journal')->insert([
            'transaction_amount' => $amount,
            'is_debit' => 1,
            'purpose' => 'goods_return_slip',
            'purpose_description' => 'Store Return Items',
            'purpose_id' => $grn_no,
            'entry_date' => date('Y-m-d')
        ]);


        Session::flash('message', 'Goods In Sucessfully');
        return redirect()->route('admin.returns.list');

    }

    // Ajax    
    public function checkScannedboxes(Request $request)
    {
        # code...
        $id = $request->id;
        $data = ReturnBox::select('barcode_no','is_scanned')->where('return_id',$id)->where(function($q){
            $q->where('is_scanned', 1)->orWhere('is_bulk_scanned', 1);
        })->get();

        $count_pro_scanned = ReturnBox::select('product_id')->selectRaw("COUNT(id) AS total_scanned")->where('return_id',$id)->where(function($q){
            $q->where('is_scanned', 1)->orWhere('is_bulk_scanned', 1);
        })->groupBy('product_id')->get();

        // return $data;
        return response()->json(array('successData'=>$data,'count_pro_scanned'=>$count_pro_scanned));
    }

    public function download_cash_slip($order_no)
    {
        $data = Returns::with('return_products')->where('order_no',$order_no)->first();
        // dd($data);
        $pckngpdfname = $order_no."";
        $pdf = Pdf::loadView('admin.returns.cashslip', compact('data','order_no'));
        return $pdf->download($pckngpdfname.'.pdf');
    }

    public function cancel($id)
    {
        // dd($id);
        $returns = Returns::find($id);

        $stock = Stock::where('return_id', $id)->first();
        if(!empty($stock)){
            $stock_id = $stock->id;
            $checkAnyItemDisbursed = StockBox::where('stock_id', $stock_id)->where('is_stock_out', 1)->count();

            if(empty($checkAnyItemDisbursed)){
                $ledger_id = $returns->ledger_id;  
                ## Make Ledger Id nullable and  Cancelled in return                
                Returns::where('id', $id)->update([
                    'is_cancelled' => 1,
                    'ledger_id' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);              
                ## Delete stock log entry of the stock id
                StockLog::where('stock_id', $stock_id)->delete();
                ## Delete Stock Boxes, Stock Products, Stock Table records of the stock id
                StockBox::where('stock_id', $stock_id)->delete();
                StockProduct::where('stock_id', $stock_id)->delete();
                Stock::where('id', $stock_id)->delete();
                ## Ledger record will be removed of the store 
                Ledger::where('id', $ledger_id)->delete();
                

            } else {
                ## ErrMsg:- Cannot cancel the order now, some items of this order are already disbursed somewhere else ....
                Session::flash('errMsg', 'Cannot cancel the order now, some items of this order are already disbursed somewhere else');
                return redirect()->route('admin.returns.list');                
            }

        } else {
            ## Cancel the order only 

            Returns::where('id', $id)->update([
                'is_cancelled' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        }

        $params = array('return_id'=>$id);
        changelogentry(Auth::user()->id,'cancel_sales_return',json_encode($params));

        Session::flash('message', 'Return Order Cancelled Sucessfully');
        return redirect()->route('admin.returns.list');

    }


}
