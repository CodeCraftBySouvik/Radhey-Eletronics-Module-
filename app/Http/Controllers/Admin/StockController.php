<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderProduct;
use App\Models\PurchaseOrderBox;
use App\Models\Stock;
use App\Models\StockProduct;
use App\Models\StockBox;
use App\Models\Ledger;
use App\Models\StockLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class StockController extends Controller
{
    //

    public function listgrn(Request $request)
    {
        $product = !empty($request->product)?$request->product:'';
        $product_name = !empty($request->product_name)?$request->product_name:'';
        $paginate = 20;
        $data = Stock::select('*')->with(['purchase_order' => function($q){
            $q->select('id','unique_id');
        }])->withCount(['stock_product' => function($p){
            $p->selectRaw('quantity');
        }]);
        $totalData = Stock::select('*');

        if(!empty($product)){
            $stock_products = StockProduct::where('product_id',$product)->get();
            if(!empty($stock_products)){
                foreach($stock_products as $stock){
                    $ids[] = $stock->stock_id;
                }
            }
            $data = $data->whereIn('id',$ids);
            $totalData = $totalData->whereIn('id',$ids);
        }        
        
        $data = $data->orderBy('id','desc')->paginate($paginate);
        $totalData = $totalData->count();

        $data = $data->appends([
            'product'=>$product,
            'product_name'=>$product_name,
            'page'=>$request->page
        ]);
        // dd($data);
        return view('admin.grn.index', compact('data','totalData','product','product_name','paginate'));
    }

    public function viewgrn($id,Request $request)
    {
        $stock = Stock::with(['purchase_order' => function($q){
            $q->select('id','unique_id','supplier_id')->with('supplier:id,name');
        }])->find($id);
        $stock_products = StockProduct::where('stock_id',$id)->get();

        return view('admin.grn.detail', compact('id','stock','stock_products'));
    }

    public function barcodes($id,Request $request)
    {
        $stock = Stock::find($id);
        $stock_box = StockBox::where('stock_id',$id)->get();
        return view('admin.grn.barcode', compact('id','stock','stock_box'));
    }

    public function searchbarcodes(Request $request)
    {
        
        $search_product_name = !empty($request->search_product_name)?$request->search_product_name:'';
        $search_product_id = !empty($request->search_product_id)?$request->search_product_id:'';
        $search_barcode = !empty($request->search_barcode)?$request->search_barcode:'';
        
        // dd('Hi');

        $data = array();
        $countData = 0;        

        $data = StockBox::select('*')->where('is_stock_out', 0);
        $countData = StockBox::where('is_stock_out', 0);
        if(!empty($search_product_id)){
            $data = $data->where('product_id',$search_product_id);
            $countData = $countData->where('product_id',$search_product_id);
        }

        if(!empty($search_barcode)){
            $data = $data->where('barcode_no', 'LIKE', '%'.$search_barcode.'%');
            $countData = $countData->where('barcode_no', 'LIKE', '%'.$search_barcode.'%');
        }

        $data = $data->orderBy('barcode_no','asc')->get();
        $countData = $countData->count();
        if(empty($search_product_id)){
            $data = array();
            $countData = 0;
        } 

        return view('admin.grn.searchbarcode', compact('data','countData','search_product_name','search_product_id','search_barcode'));

        // dd($data);
    }

    public function edit_amount(Request $request,$id)
    {
        # code...
        // die('GRN Edit Amount');
        $data = Stock::find($id);
        return view('admin.grn.edit_amount', compact('data','id'));
    }

    public function update_amount(Request $request,$id)
    {
        # code...
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
        
        $details = $params['details'];
        // dd($params);
        foreach($details as $item){
            # Stock Product Update
            StockProduct::where('stock_id',$id)->where('product_id',$item['product_id'])->update([
                'unit_price' => $item['price_per_carton'],
                'piece_price' => $item['piece_price'],
                'total_price' => $item['total_price']
            ]);
            # Stock Log Update
            StockLog::where('stock_id',$id)->where('product_id',$item['product_id'])->update([
                'carton_price' => $item['price_per_carton'],
                'piece_price' => $item['piece_price'],
                'total_price' => $item['total_price']
            ]);
            # PO Product Update
            PurchaseOrderProduct::where('purchase_order_id',$params['purchase_order_id'])->where('product_id',$item['product_id'])->update([
                'unit_price' => $item['price_per_carton'],
                'piece_price' => $item['piece_price'],
                'total_price' => $item['total_price']
            ]);
        }
        # Update Stock Amount
        Stock::where('id',$id)->update([
            'total_price' => $params['amount'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        # Update PO Amount
        PurchaseOrder::where('id',$params['purchase_order_id'])->update([
            'total_price' => $params['amount']
        ]);

        # Update Ledger
        Ledger::where('transaction_id',$params['grn_no'])->update([
            'transaction_amount' => $params['amount']
        ]);

        # Changelog Entry
        $doneby = Auth::user()->id;
        $purpose = 'edit_grn';
        $data_details = json_encode($params);
        changelogentry($doneby,$purpose,$data_details);

        Session::flash('message', 'Amount updated successfully');
        return redirect()->route('admin.grn.list');

        
    }
}
