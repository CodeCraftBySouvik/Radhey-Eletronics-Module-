<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\StockLog;
use App\Models\StockBox;
use App\Models\Ledger;
use App\Models\Journal;
use App\Models\PurchaseReturnBox;
use App\Models\PurchaseReturnProduct;
use App\Models\PurchaseReturnOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseReturnController extends Controller
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

    public function list(Request $request)
    {
        $paginate = 20;
        $product_id = !empty($request->product_id)?$request->product_id:'';
        $product_name = !empty($request->product_name)?$request->product_name:'';
        $data = PurchaseReturnOrder::select('*');
        $total = PurchaseReturnOrder::select('*');

        if(!empty($product_id)){
            
            $data = $data->whereHas('purchase_return_products', function($pro) use($product_id){
                $pro->where('product_id', $product_id);
            });

            $total = $total->whereHas('purchase_return_products', function($pro) use($product_id){
                $pro->where('product_id', $product_id);
            });
        }
        
        $data = $data->orderBy('id','desc')->paginate($paginate);
        $total = $total->count();


        return view('admin.purchasereturn.list', compact('data','total','paginate','product_id','product_name'));
    }

    public function add(Request $request)
    {       
        $suppliers = Supplier::where('status',1)->get();
        
        return view('admin.purchasereturn.add', compact('suppliers'));
    }

    public function save(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'details.*.product_id' => 'required',
            'details.*.quantity' => 'required',
        ],[
            'supplier_id.required' => 'Please choose supplier',
            'details.*.product_id.required' => 'Please add product',
            'details.*.quantity.required' => 'Please add quantity',
        ]);

        $params = $request->except('_token');
        $supplier_id = $params['supplier_id'];
        $details = $params['details'];

        foreach($details as $index => $item){
            ### Check supplier item exists in stock ... 
            $quantity = $item['quantity'];


            ### Total Supplier Received Stock Count ###
            $total_supplier_received = StockBox::where('product_id',$item['product_id'])->whereHas('stock', function($stock) use ($supplier_id){
                $stock->whereHas('purchase_order', function($po) use($supplier_id){
                    $po->where('supplier_id', $supplier_id);
                });
            })->count();

            if(!empty($total_supplier_received)){
                ### Total Supplier Available Stock Count ###
                $count_stock_supplier = StockBox::where('product_id',$item['product_id'])->whereHas('stock', function($stock) use ($supplier_id){
                    $stock->whereHas('purchase_order', function($po) use($supplier_id){
                        $po->where('supplier_id', $supplier_id);
                    });
                })->where('is_stock_out', 0)->count();
                ### Total Store Returned Available Stock Count ###
                $count_stock_non_supplier = StockBox::where('product_id',$item['product_id'])->whereHas('stock', function($q){
                    $q->whereNotNull('return_id');
                })->where('is_stock_out', 0)->count();

                ### Summation Of Suppler Received And Store Returned Available Stock ###

                $total_supplier_return_available_stock_count = ($count_stock_supplier+$count_stock_non_supplier);

                
                ### Check If Input Quantity Greater Thsn Avl Quantity ###
                if($total_supplier_return_available_stock_count < $quantity){
                    $err_msg_date = "Current stock quantity ".$total_supplier_return_available_stock_count;
                    return  redirect()->back()->withErrors(['details.'.$index.'.quantity'=> $err_msg_date])->withInput();
                }
                
            } else {
                return  redirect()->back()->withErrors(['details.'.$index.'.quantity'=> "This item had not been purchased from the supplier"])->withInput();
            }
        }

        // dd($params);

        $returnArr = array(
            'supplier_id' => $supplier_id,
            'order_no' => 'PURCHSERTRN'.genAutoIncreNoYearWise('purchase_return_orders',date('Y'),5),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        $return_id = PurchaseReturnOrder::insertGetId($returnArr);
        foreach($details as $item){
            PurchaseReturnProduct::insert([
                'return_id' => $return_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        Session::flash('message', 'Purchase Return created successfully');
        return redirect()->route('admin.purchasereturn.list');
       
    }

    public function edit($id,Request $request)
    {
        $suppliers = Supplier::where('status',1)->get();
        $order = PurchaseReturnOrder::find($id);
        $products = PurchaseReturnProduct::where('return_id',$id)->get();
        
        return view('admin.purchasereturn.edit', compact('suppliers','id','order','products'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'details.*.product_id' => 'required',
            'details.*.quantity' => 'required',
        ],[
            'supplier_id.required' => 'Please choose supplier',
            'details.*.product_id.required' => 'Please add product',
            'details.*.quantity.required' => 'Please add quantity',
        ]);

        $params = $request->except('_token');
        $return_id = $params['return_id'];
        $supplier_id = $params['supplier_id'];
        $details = $params['details'];

        $proIdArr = array();

        ### Check Existing Item Scanning Or Not ###
        $existAlreadyScanningItems = StockBox::where('purchase_return_id',$return_id)->count();
        if(!empty($existAlreadyScanningItems)){
            $custom_err_msg1 = "Cannot edit this order now, some item scanning is already started. Please back to return list.";
            return  redirect()->back()->withErrors(['custom_err_msg1'=> $custom_err_msg1])->withInput($request->all());
        }

        foreach($details as $index => $item){

            $quantity = $item['quantity'];

            ### Total Supplier Received Stock Count ###
            $total_supplier_received = StockBox::where('product_id',$item['product_id'])->whereHas('stock', function($stock) use ($supplier_id){
                $stock->whereHas('purchase_order', function($po) use($supplier_id){
                    $po->where('supplier_id', $supplier_id);
                });
            })->count();

            if(!empty($total_supplier_received)){
                ### Total Supplier Available Stock Count ###
                $count_stock_supplier = StockBox::where('product_id',$item['product_id'])->whereHas('stock', function($stock) use ($supplier_id){
                    $stock->whereHas('purchase_order', function($po) use($supplier_id){
                        $po->where('supplier_id', $supplier_id);
                    });
                })->where('is_stock_out', 0)->count();
                ### Total Store Returned Available Stock Count ###
                $count_stock_non_supplier = StockBox::where('product_id',$item['product_id'])->whereHas('stock', function($q){
                    $q->whereNotNull('return_id');
                })->where('is_stock_out', 0)->count();

                ### Summation Of Suppler Received And Store Returned Available Stock ###

                $total_supplier_return_available_stock_count = ($count_stock_supplier+$count_stock_non_supplier);

                ### Check If Input Quantity Greater Thsn Avl Quantity ###
                if($total_supplier_return_available_stock_count < $quantity){
                    $err_msg_date = "Current stock quantity ".$total_supplier_return_available_stock_count;
                    return  redirect()->back()->withErrors(['details.'.$index.'.quantity'=> $err_msg_date])->withInput();
                }                
            } else {
                return  redirect()->back()->withErrors(['details.'.$index.'.quantity'=> "This item had not been purchased from the supplier"])->withInput();
            }

            $proIdArr[] = $item['product_id'];
        }

        $oldProductIds = array();
        $old_data = PurchaseReturnProduct::where('return_id',$return_id)->get()->toArray();
        if(!empty($old_data)){
            foreach($old_data as $old){
                $oldProductIds[] = $old['product_id'];
                if(!in_array($old['product_id'],$proIdArr)){
                    PurchaseReturnProduct::where('return_id',$return_id)->where('product_id',$old['product_id'])->delete();
                }
            }
        }

        // dd($oldProductIds);
        foreach($details as $item){

            $existProducts = PurchaseReturnProduct::where('return_id',$return_id)->where('product_id',$item['product_id'])->first();

            
            
            if(!empty($existProducts)){
                PurchaseReturnProduct::where('id',$existProducts->id)->update([
                    'quantity' => $item['quantity'],
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                // dd($item['product_id']);
                PurchaseReturnProduct::insert([
                    'return_id' => $return_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }            
        }
        
        Session::flash('message', 'Purchase Return edited successfully');
        return redirect()->route('admin.purchasereturn.list');
    }

    public function details($id)
    {
        $order = PurchaseReturnOrder::find($id);
        $products = PurchaseReturnProduct::where('return_id',$id)->get();
        $boxes = PurchaseReturnBox::where('return_id',$id)->get();
        return view('admin.purchasereturn.details', compact('id','order','products','boxes'));
    }

    public function pdf($id)
    {
        $purchasereturn = PurchaseReturnOrder::find($id);
        $order_no = $purchasereturn->order_no;
        $pdfname = $order_no."";

        $pdf = Pdf::loadView('admin.purchasereturn.pdf', compact('purchasereturn'));
        return $pdf->download($pdfname.'.pdf');
    }

    public function cancel($id)
    {

        $order = PurchaseReturnOrder::find($id);
        $order_no = $order->order_no;

        ## Ledger & Journal record remove
        Ledger::where('transaction_id', $order_no)->delete();
        Journal::where('purpose_id', $order_no)->delete();

        ## Stock Box Revokes Stock Out
        StockBox::where('purchase_return_id',$id)->update([
            'scan_no'=> null, 
            'is_scanned'=>0,
            'purchase_return_id' => null,
            'is_stock_out' => 0,
            'updated_at'=>date('Y-m-d H:i:s')
        ]);

        ## Stock Log
        StockLog::where('purchase_return_id', $id)->delete();

        ## Purchase Return Box Record Delete
        PurchaseReturnBox::where('return_id', $id)->delete();        


        ## Purchase Return Order Cancelled
        PurchaseReturnOrder::where('id', $id)->update([
            'is_cancelled' => 1,
            'is_disbursed' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $params = array('purchase_return_id'=>$id);
        changelogentry(Auth::user()->id,'cancel_purchase_return',json_encode($params));

        Session::flash('message', 'Order cancelled successfully');
        return redirect()->route('admin.purchasereturn.list');
    }


}
