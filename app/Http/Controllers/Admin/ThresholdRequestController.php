<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ThresholdRequest;

class ThresholdRequestController extends Controller
{
    //

    public function index(Request $request)
    {
        $store_name = !empty($request->store_name)?$request->store_name:'';
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $product_name = !empty($request->product_name)?$request->product_name:'';
        $product_id = !empty($request->product_id)?$request->product_id:'';
        $search = !empty($request->search)?$request->search:'';
        $data = ThresholdRequest::select('*');
        $total = ThresholdRequest::select('*');

             
        if(!empty($search)){
            $data = $data->where('unique_id','LIKE','%'.$search.'%')->orWhereHas('hold_order', function($ho) use($search){
                $ho->where('order_no', 'LIKE', '%'.$search.'%');
            });
            $total = $total->where('unique_id','LIKE','%'.$search.'%')->orWhereHas('hold_order', function($ho) use($search){
                $ho->where('order_no', 'LIKE', '%'.$search.'%');
            });
        }
        if(!empty($store_id)){
            $data = $data->where('store_id', $store_id);
            $total = $total->where('store_id', $store_id);
        } 
        if(!empty($product_id)){
            $data = $data->where('product_id', $product_id);
            $total = $total->where('product_id', $product_id);
        }   
        $data = $data->orderBy('id', 'desc')->paginate(25);
        $total = $total->count();

        $data = $data->appends([
            'page' => $request->page,
            'store_id' => $store_id,
            'store_name' => $store_name,
            'product_id' => $product_id,
            'product_name' => $product_name,
            'search' => $search
        ]);
        

        return view('admin.threshold.index', compact('data','total','store_name','store_id','product_name','product_id','search'));
    }

    public function view($id)
    {
        // $data = DB::table('product_threshold_request AS ptr')->select('ptr.*','u.name AS user_name','s.store_name','p.name AS pro_name')->leftJoin('products AS p','p.id','ptr.product_id')->leftJoin('stores AS s','s.id','ptr.store_id')->leftJoin('users AS u','u.id','ptr.user_id')->where('ptr.id',$id)->first();
        $data = ThresholdRequest::find($id);
        return view('admin.threshold.add', compact('id','data'));
    }

    public function set_value(Request $request)
    {
        $request->validate([
            'interval' => 'required',
            'is_approved' => 'required'
        ],[
            'is_approved.required' => "Please choose an option"
        ]);

        $interval = $request->interval;
        $is_approved = $request->is_approved;

        $thresholdReq = ThresholdRequest::find($request->id);
        $hold_order_id = $thresholdReq->hold_order_id;

        $started_at = date('Y-m-d H:i:s');
        if($is_approved == 1){
            $expired_at = date('Y-m-d H:i:s', strtotime('+'.$interval.' days'));
            $successMsg = 'Request approved successfully. This product is now open for this store for '.$interval.' days. ';

            if(!empty($hold_order_id)){
                $successMsg = 'Request Approved and Item Has Been Attached To Master Order';
            }

            $this->attachOrderItem($request->id);
        }else if($is_approved == 2){
            /* For deny it's expired immediately */
            $expired_at = date('Y-m-d H:i:s');
            $successMsg = 'Request denied successfully.';
        }
        
        ThresholdRequest::where('id',$request->id)->update([
            'started_at' => $started_at,
            'expired_at' => $expired_at,
            'is_approved' => $request->is_approved,
            'updated_at' => $started_at
        ]);

        Session::flash('message', $successMsg); 
        return redirect()->route('admin.threshold.list');

    }

    private function attachOrderItem($id)
    {
        $data = ThresholdRequest::find($id);
        $hold_order_id = $data->hold_order_id;
        $product_id = $data->product_id;
        $price = $data->price;
        $qty = $data->qty;
        $pcs = $data->pcs;
        $product = Product::find($product_id);
        $product_pcs = $product->pcs;

        $ordProDataArr = array(
            'order_id' => $hold_order_id,
            'product_id' => $product_id,
            'product_name' => getSingleAttributeTable('products',$product_id,'name'),
            'price' => $price,
            'pcs' => ($product_pcs * $qty),
            'qty' => $qty,
            'piece_price' => ($price/$pcs),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        OrderProduct::insert($ordProDataArr);

        $orderProds = OrderProduct::where('order_id',$hold_order_id)->get()->toArray();
        $totalOrderAmount = 0;
        foreach($orderProds as $prod){
            $totalOrderAmount += ($prod['price'] * $prod['qty']);
        }
        Order::where('id',$hold_order_id)->update([
            'amount' => $totalOrderAmount,
            'final_amount' => $totalOrderAmount,
        ]);


    }


    public function view_requested_price_received_order($id)
    {
        # code...
        // $data = DB::table('product_threshold_request AS ptr')->select('ptr.*','u.name AS user_name','s.store_name','p.name AS pro_name')->leftJoin('products AS p','p.id','ptr.product_id')->leftJoin('stores AS s','s.id','ptr.store_id')->leftJoin('users AS u','u.id','ptr.user_id')->where('ptr.id',$id)->first();
        $data = ThresholdRequest::find($id);
        return view('admin.threshold.receive-customer', compact('data', 'id'));
    }

    public function save_requested_price_received_order(Request $request)
    {
        # code...
        // dd($request->all());

        $request->validate([
            'id' => 'required','integer',
            'customer_approval' => 'required',
            // 'customer_approve_note' => 'required'
        ],[
            'customer_approval.required' => "Please select an option",
            // 'customer_approve_note.required' => "Please add some note on this"
        ]);

        $params = $request->except('_token');
        $id = $params['id'];
        $customer_approval = $params['customer_approval'];
        $product_threshold_request = DB::table('product_threshold_request')->find($id);
        $user_id = $product_threshold_request->user_id;
        $store_id = $product_threshold_request->store_id;
        $product_id = $product_threshold_request->product_id;
        $price = $product_threshold_request->price;
        $qty = $product_threshold_request->qty;
        $pcs = $product_threshold_request->pcs;
        $amount = ($price * $qty);
        
        /* By default customer_approval is 1 or approved */
        /* Generate new order and order products with received if customer_approval is 1 */
        $order_no = "VZ".mt_rand();

        $order_id = Order::insertGetId([
            'order_no' => $order_no,
            'user_id' => $user_id,
            'store_id' => $store_id,
            'amount' => $amount,
            'final_amount' => $amount,
            'status' => 1,
            'is_gst' => 0,
            'comment' => $params['customer_approve_note'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $productData = Product::find($product_id);  
        $total_pcs = ($qty * $productData->pcs );      
        OrderProduct::insert([
            'order_id' => $order_id,
            'product_id' => $product_id,
            'product_name' => $productData->name,
            'product_image' => $productData->image,
            'price' => $price,
            'piece_price' => ($price / $pcs),
            'qty' => $qty,
            'pcs' => $total_pcs,
            'created_at'=>date('Y-m-d H:i:s')
        ]);

        ThresholdRequest::where('id',$id)->update([
            'customer_approve_note' => $params['customer_approve_note'],
            'customer_approval' => $customer_approval,
            'order_no' => $order_no,
            'order_id' => $order_id
        ]);

        $successMsg = "Order placed successfully";
        Session::flash('message', $successMsg);         
        return redirect()->route('admin.order.index', ['status'=>1]);
        

    }
}
