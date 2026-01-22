<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProduct;
use App\Models\PurchaseOrderBox;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Stock;
use App\Models\StockProduct;
use App\Models\StockBox;
use App\Models\StockLog;
use App\Models\Ledger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
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
                $userAccesses = userAccesses($this->designation,8);
                if(!$userAccesses){
                    abort(401);
                }
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $type = !empty($request->type)?$request->type:'po';
        $product = !empty($request->product)?$request->product:'';
        $product_name = !empty($request->product_name)?$request->product_name:'';
        $paginate = 20;
        $total = PurchaseOrder::count();
        
        $data = PurchaseOrder::select('*')
        ->withCount(['purchase_order_products' => function($q){
            $q->select(DB::raw('SUM(qty) AS total_qty'));
        }]);
        
        $total = PurchaseOrder::select('id');

        $ids = array();
        
        if(!empty($product)){
            $purchase_order_products = PurchaseOrderProduct::where('product_id',$product)->get();
            
            if(!empty($purchase_order_products)){
                foreach($purchase_order_products as $po){
                    $ids[] = $po->purchase_order_id;
                }
            }

        } 

        if(!empty($ids)){
            $data = $data->whereIn('id',$ids);
            $total = $total->whereIn('id',$ids);
        }
        
        $data = $data->orderBy('id','desc')->paginate($paginate);
        $total = $total->count();

        $data = $data->appends([
            'type' => $type,
            'product'=>$product,
            'product_name'=>$product_name,
            'page'=>$request->page
        ]);

        
        // echo $total; die;
        // dd($data);
        return view('admin.purchaseorder.index', compact('data','product','product_name','total','type','paginate'));
    }

    public function create(Request $request,$supplier_id=0)
    {
        $products = $supplier_details = array();        
        $products = DB::table('products')->select('id','name')->where('status', 1)->orderBy('name','asc')->get();
        
        $suppliers = Supplier::where('status',1)->get();
        if(!empty($supplier_id)){
            $supplier_details = Supplier::find($supplier_id);
        }
        // dd($suppliers);
        $settings = DB::table('settings')->find(1);
        return view('admin.purchaseorder.create', compact('products', 'suppliers','settings','supplier_details','supplier_id'));
    }

    public function store(Request $request)
    {
        # store...
         //dd($request->all());
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'details.*.product_id' => 'required',            
            'details.*.hsn_code' => 'required',
            'details.*.pcs' => 'required|not_in:0',
            //'details.*.weight' => 'required',
            //'details.*.weight_unit' => 'required',
            'details.*.piece_price' => 'required|not_in:0',
            'details.*.qty' => 'required|not_in:0'            
        ],[
            'details.*.product_id.required' => 'Please add product',
            'details.*.hsn_code.required' => 'Please add hsn code of product',
            'details.*.pcs.required' => 'Please add pices per carton',
            'details.*.pcs.not_in' => 'Please add pices per carton',
            //'details.*.weight.required' => 'Please add weight per carton',
            //'details.*.weight_unit.required' => 'Please add carton weight unit',
            'details.*.piece_price.required' => 'Please add price per piece',
            'details.*.piece_price.not_in' => 'Please add price per piece',
            'details.*.qty.required' => 'Please add number of carton',
            'details.*.qty.not_in' => 'Please add number of carton'
        ]);

        $params = $request->except('_token');
        // dd($params);
        $details = $params['details'];
        $idsArr = array();
        $po_total_price = 0;
        foreach($details as $item){
            // dd($item['product_id']);
            $idsArr[] = $item['product_id'];
            $po_total_price += $item['total_price'];
        }
        // dd($idsArr);
        $product_ids = implode(",",$idsArr);
        // die;

        $purchase_orders_data = array(
            'unique_id' => $params['unique_id'],
            'supplier_id' => $params['supplier_id'],     
            'product_ids' => $product_ids,           
            'address' => $params['address'],
            'state' => $params['state'],
            'city' => $params['city'],
            'country' => $params['country'],
            'pin' => $params['pin'],    
            'total_price' => $po_total_price,   
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')               
        );
        $purchase_order_id = PurchaseOrder::insertGetId($purchase_orders_data);

        foreach($details as $item){
            //if($item['weight_unit'] == 'kg'){
                //$item['weight_value'] = ($item['weight'] * 1000);
           // } else {
               // $item['weight_value'] = $item['weight'];
            //}

            PurchaseOrderProduct::insert([
                'purchase_order_id' => $purchase_order_id,
                'product_id' => $item['product_id'],
                'product' => $item['product'],
                'qty' => $item['qty'],
                'pcs' => $item['pcs'],
                'unit_price' => $item['price_per_carton'],
                'piece_price' => $item['piece_price'],
                'total_price' => $item['total_price'],
                'hsn_code' => $item['hsn_code'],
                //'weight' => $item['weight'],
                //'weight_unit' => $item['weight_unit'],
                //'weight_value' => $item['weight_value'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            /* Update product threshold and sell price */
             //$updateProductSellPrice = updateProductSellPrice($item['price_per_carton'],$item['piece_price'],$item['product_id']);
             //Product::where('id',$item['product_id'])->update([                        
                 //'hsn_code' => $item['hsn_code']
             //]);

            for($j=0;$j < ($item['qty']); $j++){
                $barcodeGen = genAutoIncreNoBarcode($item['product_id'],date('Y'));
                $barcode_no = $barcodeGen['barcode_no'];
                $code_html = $barcodeGen['code_html'];
                $code_base64_img = $barcodeGen['code_base64_img'];
                PurchaseOrderBox::insert([
                    'purchase_order_id' => $purchase_order_id,
                    'product_id' => $item['product_id'],
                    'pcs' => $item['pcs'],
                    'barcode_no' => $barcode_no,
                    'code_html' => $code_html,
                    'code_base64_img' => $code_base64_img,
                    //'po_weight_val' => $item['weight_value']
                ]);
            }

        }

        /* changelogentry */
        changelogentry(Auth::user()->id,'add_po',json_encode($details));

        Session::flash('message', 'Purchase order created successfully');
        return redirect()->route('admin.purchaseorder.index', ['type'=>'po']);
    }

    public function show(Request $request, $id)
    {
        $code_html_arr = array();
        $po = PurchaseOrder::find($id);
        $unique_id = $po->unique_id;
        $data = PurchaseOrderProduct::where('purchase_order_id',$id)->get();
        
        return view('admin.purchaseorder.detail', compact('po','id','unique_id','data'));
    }

    public function barcodes(Request $request,$id)
    {
        // $purchase_order_boxes = PurchaseOrderBox::where('purchase_order_id',$id)->where('is_archived', 0)->get()->toarray();

        $purchase_order_boxes = DB::table('purchase_order_boxes AS psb')->select('psb.*','p.name')->leftJoin('products AS p','p.id','psb.product_id')->where('purchase_order_id',$id)->where('psb.is_archived', 0)->get()->toarray();
        $new_arr = array_chunk($purchase_order_boxes,10,true);
        // dd($new_arr);

        return view('admin.purchaseorder.barcode', compact('purchase_order_boxes','id','new_arr'));
    }

    
    public function edit(Request $request, $id)
    {
        $po = PurchaseOrder::find($id);
        $data = PurchaseOrderProduct::where('purchase_order_id',$id)->get();

        $products = DB::table('products')->where('status',1)->orderBy('name','asc')->get();
        return view('admin.purchaseorder.edit', compact('po','data', 'products'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'details.*.product_id' => 'required',            
            'details.*.hsn_code' => 'required',
            'details.*.pcs' => 'required|not_in:0',
            //'details.*.weight' => 'required',
            //'details.*.weight_unit' => 'required',
            'details.*.piece_price' => 'required|not_in:0',
            'details.*.qty' => 'required|not_in:0'            
        ],[
            'details.*.product_id.required' => 'Please add product',
            'details.*.hsn_code.required' => 'Please add hsn code of product',
            'details.*.pcs.required' => 'Please add pices per carton',
            'details.*.pcs.not_in' => 'Please add pices per carton',
            //'details.*.weight.required' => 'Please add weight per carton',
            //'details.*.weight_unit.required' => 'Please add carton weight unit',
            'details.*.piece_price.required' => 'Please add price per piece',
            'details.*.piece_price.not_in' => 'Please add price per piece',
            'details.*.qty.required' => 'Please add number of carton',
            'details.*.qty.not_in' => 'Please add number of carton'
        ]);

        $params = $request->except('_token');        
        $details = $params['details'];

        // dd($params);

        PurchaseOrder::where('id',$params['id'])->update([
            'total_price' => $params['total_po_price'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        
        
        foreach($details as $item){
            //if($item['weight_unit'] == 'kg'){
                //$item['weight_value'] = ($item['weight'] * 1000);
            //} else {
                //$item['weight_value'] = $item['weight'];
            //}
            PurchaseOrderProduct::where('purchase_order_id',$params['id'])->where('product_id',$item['product_id'])->update([
                'qty' => $item['qty'],
                'pcs' => $item['pcs'],
                'unit_price' => $item['price_per_carton'],
                'piece_price' => $item['piece_price'],
                'total_price' => $item['total_price'],
                'hsn_code' => $item['hsn_code'],
                //'weight' => $item['weight'],
                //'weight_unit' => $item['weight_unit'],
                //'weight_value' => $item['weight_value'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            /* Update product threshold and sell price */
            //$updateProductSellPrice = updateProductSellPrice($item['price_per_carton'],$item['piece_price'],$item['product_id']);
            //Product::where('id',$item['product_id'])->update([                        
                //'hsn_code' => $item['hsn_code']
            //]);

            if($item['isNoCtnChanged'] == 1){
                if($item['qty'] > $item['oldCtnNo']){
                    $rest = ($item['qty'] - $item['oldCtnNo']);
                    // echo 'product_id:- '.$item['product_id'].' Add New:- '.$rest;
                    // echo '<br/>';
                    for($j=0;$j < ($rest); $j++){
                        $barcodeGen = genAutoIncreNoBarcode($item['product_id'],date('Y'));
                        $barcode_no = $barcodeGen['barcode_no'];
                        $code_html = $barcodeGen['code_html'];
                        $code_base64_img = $barcodeGen['code_base64_img'];
                        PurchaseOrderBox::insert([
                            'purchase_order_id' => $params['id'],
                            'product_id' => $item['product_id'],
                            'pcs' => $item['pcs'],
                            'barcode_no' => $barcode_no,
                            'code_html' => $code_html,
                            'code_base64_img' => $code_base64_img,
                            //'po_weight_val' => $item['weight_value']
                        ]);
                    }

                } else {
                    $rest = ($item['oldCtnNo'] - $item['qty']);
                    // echo 'product_id:- '.$item['product_id'].' Delete Old:- '.$rest;
                    // echo '<br/>';
                    $old_rest_box = PurchaseOrderBox::where('purchase_order_id',$params['id'])->where('product_id',$item['product_id'])->orderBy('id','desc')->take($rest)->delete();

                }
            }

            /* Update Pcs Value In Box If Changed In PO Product Row */
            PurchaseOrderBox::where('purchase_order_id',$params['id'])->where('product_id',$item['product_id'])->update([
                'pcs' => $item['pcs'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            
        
        }

        /* changelogentry */
        changelogentry(Auth::user()->id,'edit_po',json_encode($details));

        // dd($params);

        Session::flash('message', 'Purchase order updated successfully');
        return redirect()->route('admin.purchaseorder.index', ['type'=>'po']);
    }

    public function showboxes(Request $request,$id)
    {
        $search = !empty($request->search)?$request->search:'';
        
        $data = PurchaseOrderBox::with('product')->where('purchase_order_id',$id)->where('is_archived', 0);  
        $totalData = PurchaseOrderBox::with('product')->where('purchase_order_id',$id)->where('is_archived', 0);  
        if(!empty($search)){
            $data = $data->where(function($q) use ($search){
                $q->where('barcode_no','LIKE', '%'.$search.'%')->orWhereHas('product', function ($product) use ($search) {
                    $product->where('name', 'LIKE','%'.$search.'%');
                });
            });

            $totalData = $totalData->where(function($q) use ($search){
                $q->where('barcode_no','LIKE', '%'.$search.'%')->orWhereHas('product', function ($product) use ($search) {
                    $product->where('name', 'LIKE','%'.$search.'%');
                });
            });
        }
        $data = $data->orderBy('id','asc')->get()->groupBy('product.id');
        $totalData = $totalData->count();
        // dd($data);
        
        return view('admin.purchaseorder.showboxes', compact('data','totalData','search','id'));
    }

    public function grn(Request $request,$id)
    {
        # code...
        $search = !empty($request->search)?$request->search:'';
        $goods_in_type = !empty($request->goods_in_type)?$request->goods_in_type:'';
        $purchaseorder = PurchaseOrder::find($id);
        $unique_id = $purchaseorder->unique_id;

        $data = PurchaseOrderBox::with('product')->where('purchase_order_id',$id)->where('is_archived', 0)->where('is_goods_in', 0);        
        if(!empty($search)){
            $data = $data->where(function($q) use ($search){
                $q->where('barcode_no','LIKE', '%'.$search.'%')->orWhereHas('product', function ($product) use ($search) {
                    $product->where('name', 'LIKE','%'.$search.'%');
                });
            });
        }
        $data = $data->get()->sortBy('product_id')->groupBy('product.id');  

        if(!empty($goods_in_type)){
            PurchaseOrder::where('id',$id)->update(['goods_in_type'=>$goods_in_type]);
        }

        $total_checked = PurchaseOrderBox::where('purchase_order_id',$id)->where('is_archived', 0)->where('is_goods_in', 0)->where(function($q){
            $q->where('is_scanned', 1)->orWhere('is_bulk_scanned', 1);
        })->count();

        // dd($total_checked);

        return view('admin.purchaseorder.grn', compact('purchaseorder','goods_in_type','data','unique_id','id','search','total_checked'));
    }

    public function pobulkscan(Request $request)
    {
        # PO Bulk Scan...

        // dd($request->all());

        $purchase_order_id = !empty($request->purchase_order_id)?$request->purchase_order_id:'';
        $product_id = !empty($request->product_id)?$request->product_id:'';
        $is_bulk_scanned = $request->is_bulk_scanned;
        $is_scanned = $request->is_scanned;
        $data = PurchaseOrderBox::where('purchase_order_id',$purchase_order_id)->where('product_id',$product_id)->where(function($q){
            $q->where('is_scanned', 0)->orWhere('is_bulk_scanned', 0);
        })->get();

        // dd($data);
        
        if(!empty($data)){
            foreach($data as $item){
                PurchaseOrderBox::where('id',$item->id)->update([
                    'is_bulk_scanned'=>$is_bulk_scanned,
                    // 'is_goods_in'=>$is_scanned
                ]);
            }
        }

        return 1;
        
    }

    public function archiveBox($id,$product_id,$barcode_no,$getQueryString='')
    {
        # remove box and archive box to another place ...

        $purchase_order_box =  PurchaseOrderBox::where('purchase_order_id',$id)->where('product_id',$product_id)->where('barcode_no',$barcode_no)->first();

        if(!empty($purchase_order_box)){
            // dd($getQueryString);
            PurchaseOrderBox::where('id',$purchase_order_box->id)->update([
                'is_archived' => 1
            ]);

            $po = PurchaseOrderProduct::where('purchase_order_id',$id)->where('product_id',$product_id)->first();
            $qty = $po->qty;
            $qty = $qty-1;
            /* Substraction from total amount */
            $unit_price = $po->unit_price;
            $total_price = $po->total_price;
            $new_total_price = ($total_price - $unit_price);
            // echo $qty; exit;
            PurchaseOrderProduct::where('purchase_order_id', $id)->where('product_id',$product_id)->update(['qty' => $qty , 'total_price' => $new_total_price]);
            $total_po_price = PurchaseOrderProduct::where('purchase_order_id',$id)->sum('total_price');
            PurchaseOrder::where('id',$id)->update(['total_price'=>$total_po_price]);

            Session::flash('message', 'Carton '.$barcode_no.' is archived suceesfully'); 
            return redirect()->route('admin.purchaseorder.grn', [$id,$getQueryString]);
            
        } else {
            return  redirect()->back()->withInput();
        }
        
    }

    public function archived($id)
    {
        # Archived boxes ...
        
        $purchase_order_boxes = DB::table('purchase_order_boxes AS psb')->select('psb.*','p.name')->leftJoin('products AS p','p.id','psb.product_id')->where('purchase_order_id',$id)->where('psb.is_archived', 1)->get()->toarray();
        $new_arr = array_chunk($purchase_order_boxes,10,true);
        // dd($new_arr);

        return view('admin.purchaseorder.archived', compact('purchase_order_boxes','id','new_arr'));
    }

    public function saveinventory(Request $request)
    {
        $grn_no = getRandString(16);
        $params = $request->except('_token');
        $request->validate([
            'barcode_no' => 'required'
        ],[
            'barcode_no.required'=>'Please scan at least one barcode'
        ]);
        
        // dd($params);

        
        $stock_id = Stock::insertGetId([
            'purchase_order_id' => $params['id'],
            'po_unique_id' => $params['unique_id'],
            'goods_in_type' => $params['goods_in_type'],
            'grn_no' => $grn_no
        ]);

        $barcode_no = $params['barcode_no'];
        
        foreach($barcode_no as $barcode){
            $check_same_goods_in_barcode_exists = StockBox::where('barcode_no',$barcode)->first();
            if(!empty($check_same_goods_in_barcode_exists)){
                return  redirect()->back()->withErrors([
                    'duplicate_barcode_err_msg'=> "Same barcode should not be for GRN. Please REFRESH this page and submit.",
                    ])->withInput(); 
            }
        }

        foreach($barcode_no as $barcode){
            $getBarcodeDetails = getBarcodeDetails($barcode);
            StockBox::insert([
                'stock_id' => $stock_id,
                'product_id' => $getBarcodeDetails['product_id'],
                'pcs' => $getBarcodeDetails['pcs'],
                'barcode_no' => $barcode,
                'code_html' => $getBarcodeDetails['code_html'],
                'code_base64_img' => $getBarcodeDetails['code_base64_img'],
                'stock_in_weight_val' => $getBarcodeDetails['po_weight_val'] 
            ]); 

              // Update Product threshold and sell price
            $updateProductSellPrice = updateProductSellPrice(
                $getBarcodeDetails['price_per_carton'],  // Price per carton
                $getBarcodeDetails['piece_price'],      // Price per piece (can be used if needed)
                $getBarcodeDetails['product_id']
            );
             // Update HSN code
            Product::where('id', $getBarcodeDetails['product_id'])->update([
                'hsn_code' => $getBarcodeDetails['hsn_code']
            ]);
        }

        
        PurchaseOrderBox::whereIn('barcode_no',$barcode_no)->update(['is_goods_in' => 1]);
        
        ## Changelogentry
        changelogentry(Auth::user()->id,'grn',json_encode($params));

        $this->setStockProduct($params['id'],$stock_id,$grn_no,$barcode_no);
        

        $successMsg = "Stock inventory has been generated with GRN successfully";        
        Session::flash('message', $successMsg); 
        return redirect()->route('admin.grn.list');
          
    }

    
    private function setStockProduct($purchase_order_id,$stock_id,$grn_no,$barcodes){
        ## Set Stock Product Quanties and Prices Against Barcodes

        $stock_boxes = StockBox::select('product_id')->selectRaw("COUNT(barcode_no) AS quantity,SUM(pcs) AS total_pcs")->whereIn('barcode_no',$barcodes)->groupBy('product_id')->get()->toArray();

        $totalPriceSum = 0;
        $product_ids = '';
        $proids = array();
        foreach($stock_boxes as $stock_product){
            $proids[] = $stock_product['product_id'];
            $purchase_order_product = PurchaseOrderProduct::where('purchase_order_id',$purchase_order_id)->where('product_id',$stock_product['product_id'])->first();
            $unit_price = $purchase_order_product->unit_price;
            $piece_price = $purchase_order_product->piece_price;
            $quantity = $stock_product['quantity'];
            $total_price = ($quantity * $unit_price);
            $totalPriceSum += $total_price;
            $stockProArr = array(
                'stock_id' => $stock_id,
                'product_id' => $stock_product['product_id'],
                'quantity' => $stock_product['quantity'],
                'unit_price' => $unit_price,
                'piece_price' => $piece_price,
                'total_price' => $total_price,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            StockProduct::insert($stockProArr);


            # Stock Log Entry
            $stockLogArr = array(
                'product_id' => $stock_product['product_id'],
                'entry_date' => date('Y-m-d'),
                'stock_id' => $stock_id,
                'quantity' => $stock_product['quantity'],
                'pcs' => $stock_product['total_pcs'],
                'piece_price' => $piece_price,
                'carton_price' => $unit_price,
                'total_price' => $total_price,
                'type' => 'in',
                'entry_type' => 'grn',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            StockLog::insert($stockLogArr);
        }
        $product_ids = implode(",", $proids);
        $stockUpdateArr = array(
            'total_price' => $totalPriceSum,
            'product_ids' => $product_ids,
            'updated_at' => date('Y-m-d H:i:s')
        );
        Stock::where('id',$stock_id)->update($stockUpdateArr);

        $purchase_order = PurchaseOrder::find($purchase_order_id);
        $supplier_id = $purchase_order->supplier_id;
        ##  Supplier Ledger Entry
        Ledger::insert([
            'user_type' => 'supplier',
            'supplier_id' => $supplier_id,
            'transaction_id' => $grn_no,
            'transaction_amount' => $totalPriceSum,
            'entry_date' => date('Y-m-d'),
            'is_credit' => 1,'purpose' => 'goods_received_note',
            'purpose_description' => 'Goods Received Note'
        ]);

        $total_box = PurchaseOrderBox::where('purchase_order_id',$purchase_order_id)->where('is_archived', 0)->count();
        $total_scanned_box = PurchaseOrderBox::where('purchase_order_id',$purchase_order_id)->where('is_archived', 0)->where('is_goods_in', 1)->count();

        if($total_box == $total_scanned_box){
            PurchaseOrder::where('id',$purchase_order_id)->update([
                'status' => 2
            ]);
        }

    }

    public function getProductsSupplier(Request $request)
    {
        $supplier_id = $request->supplier_id;
        $products = DB::table('products')->select('*')->where('supplier_id',$supplier_id)->get();
        return $products;
    }

    // Ajax    
    public function checkScannedboxes(Request $request)
    {
        # code...
        $id = $request->id;
        $data = PurchaseOrderBox::select('barcode_no','is_scanned','scanned_weight_val')->where('purchase_order_id',$id)->where('is_goods_in', 0)->where('is_archived', 0)->where(function($q){
            $q->where('is_scanned', 1)->orWhere('is_bulk_scanned', 1);
        })->get();

        $count_pro_scanned = PurchaseOrderBox::select('product_id')->selectRaw("COUNT(id) AS total_scanned")->where('purchase_order_id',$id)->where('is_goods_in', 0)->where('is_archived', 0)->where(function($q){
            $q->where('is_scanned', 1)->orWhere('is_bulk_scanned', 1);
        })->groupBy('product_id')->get();

        // return $data;
        return response()->json(array('successData'=>$data,'count_pro_scanned'=>$count_pro_scanned));
    }

    public function pdf($id)
    {
        $purchaseorder = PurchaseOrder::find($id);
        $unique_id = $purchaseorder->unique_id;
        $popdfname = $unique_id."";

        $pdf = Pdf::loadView('admin.purchaseorder.pdf', compact('purchaseorder'));
        return $pdf->download($popdfname.'.pdf');
    }
    
}
