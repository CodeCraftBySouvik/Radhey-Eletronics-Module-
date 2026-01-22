<?php

namespace App\Http\Controllers\Admin;

use App\Interfaces\OrderInterface;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Store;
use App\Models\ThresholdRequest;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Changelog;
use App\Models\Product;

class OrderController extends Controller
{
    // private OrderInterface $orderRepository;

    public function __construct(OrderInterface $orderRepository,Request $request)
    {
        $this->orderRepository = $orderRepository;
        $this->middleware('auth:web');
        $this->middleware(function ($request, $next) {
            $this->id = Auth::user()->id;
            $this->type = Auth::user()->type;
            $this->designation = Auth::user()->designation;
            // dd($this->type);
            if($this->type == 2){
                $userAccesses = userAccesses($this->designation,9);
                if(!$userAccesses){
                    abort(401);
                }
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $paginate = !empty($request->paginate)?$request->paginate:25;
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $store_name = !empty($request->store_name)?$request->store_name:'';
        $staff_id = !empty($request->staff_id)?$request->staff_id:'';
        $search = !empty($request->search)?$request->search:'';
        $status = !empty($request->status)?$request->status:1;

        $data = Order::select('orders.*')->selectRaw("(SELECT GROUP_CONCAT(op2.product_id) FROM order_products AS op2 WHERE op2.order_id = orders.id ) AS pro_ids ")->with('stores')->with('orderProducts')->with('users');

        $totalData = Order::select('*');

        if(!empty($status)){
            $data = $data->where('orders.status', $status);
            $totalData = $totalData->where('orders.status', $status);
        }
        if(!empty($store_id)){
            $data = $data->where('orders.store_id', $store_id);
            $totalData = $totalData->where('orders.store_id', $store_id);
        }
        if(!empty($staff_id)){
            $data = $data->where('orders.user_id', $staff_id);
            $totalData = $totalData->where('orders.user_id', $staff_id);
        }
        if(!empty($search)){
            $data = $data->where('orders.order_no', 'LIKE', '%'.$search.'%');
            $totalData = $totalData->where('orders.order_no', 'LIKE', '%'.$search.'%');
        }
        
               

        if(!empty($status)){
            if(in_array($status, [1,2,4])){
                $data = $data->orderBy('orders.id','desc');
            }else{
                $data = $data->orderBy('orders.updated_at','desc');
            }
        } else {
            $data = $data->orderBy('orders.id','desc');
        }
        
        
        $data = $data->paginate($paginate);
        $totalData = $totalData->count();

        // dd($data);

        foreach($data as $d){
            $order_products = OrderProduct::where('order_id',$d->id)->get();
            if($d->status == 2){
                $order_products = OrderProduct::where('order_id',$d->id)->whereRaw("qty != release_qty")->get();
            }
            $d->order_products = $order_products;
        }
             
        // dd($data);

        $data = $data->appends([
            'status' => $status,
            'store_id' => $store_id,
            'search' => $search,
            'staff_id' => $staff_id,
            'page' => $request->page,
            'paginate' => $paginate
        ]);

        $users = User::select('id','name')->get();
        $stores = Store::get();
        return view('admin.order.index', compact('data','totalData','users','stores','status','paginate','staff_id','store_id','store_name','search'));
    }

    public function indexStatus(Request $request, $status)
    {
        $data = $this->orderRepository->listByStatus($status);
        return view('admin.order.index', compact('data'));
    }

    public function add(Request $request)
    {
        $users = User::select('id','name')->get();
        return view('admin.order.add', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'store_id' => 'required',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.piece_price' => 'required|not_in:0',
            'details.*.qty' => 'required|not_in:0'    
        ],[
            'store_id.required' => 'Please select store',
            'details.*.product_id.required' => 'Please add product',
            'details.*.piece_price.required' => 'Please add price per pcs',
            'details.*.qty.required' => 'Please add no of catons'
        ]);

        $params = $request->except('_token');
        $details = $params['details'];

        
        
        $thresholdErrMsg = array(
            
        );
        $isThresholdTrue = false;
        $thresholdErrMsgTxt = "";
        $thresholdErrMsgText = "Order value can not be changed later for this threshold condition.Ensure the order quantity and price for this order item is correct. ";
        foreach($details as $key => $item){
            // dd($item['product']);
            $product_id = $item['product_id'];
            $piece_price = $item['piece_price'];
            $product = Product::find($product_id);
            $sell_price = $product->sell_price;
            
            if($piece_price < $sell_price){
                $isThresholdTrue = true;
                $thresholdErrMsgTxt .= "".$item['product']." , ";
                $thresholdErrMsg = array_merge($thresholdErrMsg,['details.'.$key.'.piece_price'=> "Price under threshold"]);
            }
        }
        if($isThresholdTrue){
            $thresholdErrMsgTxt .= " are under threshold price. ";
            $thresholdErrMsgTxt .= " You can proceed with this order excluding the threshold items/s or regenerate the order once again. ";
            $thresholdErrMsg = array_merge($thresholdErrMsg, ['thresholdErrMsg' => $thresholdErrMsgTxt]);
            $thresholdErrMsg = array_merge($thresholdErrMsg, ['thresholdErrMsgText' => $thresholdErrMsgText]);

            return  redirect()->back()->withErrors($thresholdErrMsg)->withInput();
        }
        $orderData = array(
            'order_no' => "AGNI".mt_rand(),
            'user_id' => $params['user_id'],
            'store_id' => $params['store_id'],
            'amount' => $params['amount'],
            'final_amount' => $params['amount'],
            'created_from' => 'web',
            'is_gst' => $params['is_gst'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_by' => Auth::user()->id
        );
        $id = Order::insertGetId($orderData);
        foreach($details as $item){
            $orderProData = array(
                'order_id' => $id,
                'product_id' => $item['product_id'],
                'product_name' => $item['product'],
                'piece_price' => $item['piece_price'],
                'price' => $item['price'],
                'qty' => $item['qty'],
                'pcs' => $item['pcs'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            OrderProduct::insert($orderProData);
        }

        Session::flash('message', 'Order created successfully'); 
        return redirect()->route('admin.order.index',['status'=>1]); 


    }

    public function store_threshold(Request $request)
    {
        $params = $request->except('_token');
        // dd($params);

        $orderData = array(
            'order_no' => "AGNI".mt_rand(),
            'user_id' => $params['user_id'],
            'store_id' => $params['store_id'],
            'created_from' => 'web',
            'is_gst' => $params['is_gst'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'created_by' => Auth::user()->id
        );
        $id = Order::insertGetId($orderData);

        $details = $params['details'];

        
        foreach($details as $key => $item){
            $product_id = $item['product_id'];
            $piece_price = $item['piece_price'];
            $product = Product::find($product_id);
            $sell_price = $product->sell_price;
            $threshold_price = $product->threshold_price;

            if($piece_price < $sell_price){                
                $unique_id = genAutoIncreNoYearWise('product_threshold_request',date('Y'),5);
                $unique_id = "THRESH".$unique_id;                    
                $thresholdArr = array(
                    'unique_id' => $unique_id,
                    'user_id' => $params['user_id'],
                    'store_id' => $params['store_id'],
                    'product_id' => $item['product_id'],
                    'price' => $item['price'],
                    'threshold_price' => $threshold_price,
                    'sell_price' => $sell_price,
                    'pcs' => $item['propcs'],
                    'qty' => $item['qty'],
                    'hold_order_id' => $id,
                    'started_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );            
                ThresholdRequest::insert($thresholdArr);                
            } else {
                ## Normal Order Products
                $orderProData = array(
                    'order_id' => $id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product'],
                    'piece_price' => $item['piece_price'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'pcs' => $item['pcs'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                OrderProduct::insert($orderProData);
            }
        }

        $orderProds = OrderProduct::where('order_id',$id)->get()->toArray();
        $totalOrderAmount = 0;
        foreach($orderProds as $prod){
            $totalOrderAmount += ($prod['price'] * $prod['qty']);
        }
        Order::where('id',$id)->update([
            'amount' => $totalOrderAmount,
            'final_amount' => $totalOrderAmount,
        ]);

        Session::flash('message', 'Order created successfully, Let approve other items which are under threshold. '); 
        return redirect()->route('admin.order.index',['status'=>1]); 


    }

    public function show(Request $request, $id)
    {
        $data = Order::with('stores')->with('users')->find($id);
        // $data = $datas[0];
        //$data = $this->orderRepository->listById($id);

        // dd($data->orderProducts);
        // dd($data);
        
        return view('admin.order.detail', compact('data', 'id'));
    }

    public function status(Request $request, $id, $status)
    {
        $storeData = $this->orderRepository->toggle($id, $status);

        if ($storeData) {
            if($status == 3){
                Session::flash('message', 'Order cancelled successfully'); 
            }
            return redirect()->route('admin.order.index',['status'=>$status]);
        } else {
            return redirect()->route('admin.order.index',['status'=>$status]);
        }
    }

    public function edit(Request $request,$id)
    {
        $order = Order::with('stores')->find($id);
        $items = OrderProduct::where('order_id',$id)->get();
        // dd($items);
        return view('admin.order.edit', compact('id','order','items'));
    }

    public function update(Request $request, $id)
    {
        # Update Order
        $request->validate([
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.piece_price' => 'required|not_in:0',
            'details.*.qty' => 'required|not_in:0'    
        ],[
            'details.*.product_id.required' => 'Please add product',
            'details.*.piece_price.required' => 'Please add price per pcs',
            'details.*.qty.required' => 'Please add no of catons'
        ]);

        $params = $request->except('_token');
        $details = $params['details'];

        $thresholdErrMsg = array(
            
        );
        $isThresholdTrue = false;
        $thresholdErrMsgTxt = "";
        $thresholdErrMsgText = "Order value can not be changed later for this threshold condition.Ensure the order quantity and price for this order item is correct. ";
        // dd($details);
        foreach($details as $key => $item){
            // dd($item['product']);
            $product_id = $item['product_id'];
            $piece_price = $item['piece_price'];
            $product = Product::find($product_id);
            $sell_price = $product->sell_price;

            $checkThreshold = ThresholdRequest::where('hold_order_id',$id)->where('product_id',$product_id)->where('price',$item['price'])->where('qty', $item['qty'])->first();

            // dd($checkThreshold);

            if(!empty($checkThreshold)){
                $piece_price = ($checkThreshold->price / $checkThreshold->pcs);
                // dd($piece_price);
            } else {
                if($piece_price < $sell_price){
                    $isThresholdTrue = true;
                    $thresholdErrMsgTxt .= "".$item['product']." , ";
                    $thresholdErrMsg = array_merge($thresholdErrMsg,['details.'.$key.'.piece_price'=> "Price under threshold"]);
                }
            }
            
            
        }
        if($isThresholdTrue){
            $thresholdErrMsgTxt .= " are under threshold price. ";
            $thresholdErrMsgTxt .= " You can proceed with this order excluding the threshold items/s or regenerate the order once again. ";
            $thresholdErrMsg = array_merge($thresholdErrMsg, ['thresholdErrMsg' => $thresholdErrMsgTxt]);
            $thresholdErrMsg = array_merge($thresholdErrMsg, ['thresholdErrMsgText' => $thresholdErrMsgText]);

            return  redirect()->back()->withErrors($thresholdErrMsg)->withInput();
        }

        // dd($params);

        $oldProIds = $currentProIds = $removeProIdArr = array();
        $old_order_products = OrderProduct::select('product_id')->where('order_id',$id)->get();
        foreach($old_order_products as $pro){
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

        if(!empty($removeProIdArr)){
            foreach($removeProIdArr as $value){
                OrderProduct::where('order_id',$id)->where('product_id',$value)->delete();
            }
        }
        // echo 'oldProIds:- <pre>'; print_r($oldProIds);
        // echo 'currentProIds:- <pre>'; print_r($currentProIds);
        // echo 'removeProIdArr:- <pre>'; print_r($removeProIdArr);
        // die;
        Order::where('id',$id)->update([
            'amount' => $params['amount'],
            'final_amount' => $params['amount'],
            'comment' => 'Edited from backend',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => Auth::user()->id
        ]);
        
        if(!empty($details)){            
            foreach($details as $item){
                if($item['isOld'] == 0){
                    # Insert Order Products

                    $addOrderPro = array(
                        'order_id' => $id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product'],
                        'price' => $item['price'],
                        'piece_price' => $item['piece_price'],
                        'qty' => $item['qty'],
                        'pcs' => $item['pcs'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    );

                    OrderProduct::insert($addOrderPro);
                }
                if($item['isOld'] == 1){
                    # Update Order Product
                    $editOrderPro = array(
                        'price' => $item['price'],
                        'piece_price' => $item['piece_price'],
                        'qty' => $item['qty'],
                        'pcs' => $item['pcs'],
                        'updated_at' => date('Y-m-d H:i:s')
                    );

                    OrderProduct::where('order_id',$id)->where('product_id',$item['product_id'])->update($editOrderPro);
                }
            }
        }

        /* Changelog Entry */

        Changelog::insert([
            'doneby' => Auth::user()->id,
            'purpose' => 'edit_order',
            'data_details' => json_encode($params),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Session::flash('message', 'Order updated successfully'); 
        return redirect()->route('admin.order.index',['status'=>1]); 

    }

    public function update_threshold(Request $request, $id)
    {
        $params = $request->except('_token');
        $details = $params['details'];
        // dd($params);
        OrderProduct::where('order_id',$id)->delete();
        // ThresholdRequest::where('hold_order_id',$id)->delete();
        $order = Order::find($id);

        foreach($details as $key => $item){
            $product_id = $item['product_id'];
            $piece_price = $item['piece_price'];
            $product = Product::find($product_id);
            $sell_price = $product->sell_price;
            $threshold_price = $product->threshold_price;

            if($piece_price < $sell_price){                
                $unique_id = genAutoIncreNoYearWise('product_threshold_request',date('Y'),5);
                $unique_id = "THRESH".$unique_id;                    
                $thresholdArr = array(
                    'unique_id' => $unique_id,
                    'user_id' => $order->user_id,
                    'store_id' => $params['store_id'],
                    'product_id' => $item['product_id'],
                    'price' => $item['price'],
                    'threshold_price' => $threshold_price,
                    'sell_price' => $sell_price,
                    'pcs' => $item['propcs'],
                    'qty' => $item['qty'],
                    'hold_order_id' => $id,
                    'started_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );            
                ThresholdRequest::insert($thresholdArr);                
            } else {
                ## Normal Order Products
                $orderProData = array(
                    'order_id' => $id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product'],
                    'piece_price' => $item['piece_price'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'pcs' => $item['pcs'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                );
                OrderProduct::insert($orderProData);
            }
        }

        $orderProds = OrderProduct::where('order_id',$id)->get()->toArray();
        $totalOrderAmount = 0;
        foreach($orderProds as $prod){
            $totalOrderAmount += ($prod['price'] * $prod['qty']);
        }
        Order::where('id',$id)->update([
            'amount' => $totalOrderAmount,
            'final_amount' => $totalOrderAmount,
        ]);

        /* Changelog Entry */

        Changelog::insert([
            'doneby' => Auth::user()->id,
            'purpose' => 'edit_order',
            'data_details' => json_encode($params),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Session::flash('message', 'Order created successfully, Let approve other items which are under threshold. '); 
        return redirect()->route('admin.order.index',['status'=>1]); 
    }


    


    
}