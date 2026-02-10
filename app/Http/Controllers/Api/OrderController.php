<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\OrderInterface;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Cart;
use App\Models\PackingslipNew1;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\User;

class OrderController extends Controller
{
    public function __construct(OrderInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    
    public function view($userId,$storeId): JsonResponse
    {

        $order = Order::with('stores:id,store_name,bussiness_name')->with('users:id,name,mobile')->with(['orderProducts' => function($q){
            $q->select('id','order_id','product_id','product_name','price','piece_price','qty','pcs');
        }]);
        
        $order = $order->where('store_id',$storeId)->where('status', '!=', 3)->orderBy('id','desc')->get();

        if(!empty($order)){
            foreach($order as $item){
                $total_ctn = OrderProduct::where('order_id',$item->id)->sum('qty');
                $item->total_ctn = $total_ctn;

                $packingslip = PackingslipNew1::where('order_id',$item->id)->first();
                $is_disbursed = 0;
                if(!empty($packingslip)){
                    $is_disbursed = $packingslip->is_disbursed;
                }
                $item->is_disbursed = $is_disbursed;

            }
        }
        
        return response()->json(['error'=>false, 'resp'=>'Order data fetched successfully','data'=>$order]);
    }
    

    public function placeorder(Request $request): JsonResponse
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
            'store_id' => ['required', 'integer', 'min:1', 'exists:stores,id'],
            'comment' => ['string'],
            'order_location' => ['string','nullable'],
            'order_lat' => ['string','nullable'],
            'order_lng' => ['string','nullable'],
            'attendance_id' => ['nullable','exists:user_attendances,id'],
            'is_gst' => ['required','string', 'in:0,1']
        ]);
        $params = $request->except('_token');
        
        if (!$validator->fails()) {

            if(!in_array($params['user_id'],[1,2])){
                if(empty($request->order_lat)){
                    return response()->json(['status'=>400,'message'=>"Please add latitude"], 400);
                }
                if(empty($request->order_lng)){
                    return response()->json(['status'=>400,'message'=>"Please add longitude"], 400);
                }
                if(empty($request->attendance_id)){
                    return response()->json(['status'=>400,'message'=>"Please add attendance id"], 400);
                }

                $checkattendance = DB::table('user_attendances')->find($params['attendance_id']);

                if($checkattendance->user_id != $params['user_id']){
                    return response()->json(
                        [
                            'error' => true,
                            'message' => "This is not you attendacne id ",
                            'data' => (object) []
                        ],
                        200
                    );
                }
                if($checkattendance->start_date != date('Y-m-d')){
                    return response()->json(
                        [
                            'error' => true,
                            'message' => "This is not today's attendance id",
                            'data' => (object) []
                        ],
                        200
                    );
                }
            }            
            $checkCart = Cart::where('user_id', $params['user_id'])->first();
            if(empty($checkCart)){
                return response()->json(
                    [
                        'error' => true,
                        'message' => "No items found in cart",
                        'data' => (object) []
                    ],
                    200
                );
            }
            if(!in_array($params['user_id'], [1,2])){
                $attendance_id = $params['attendance_id'];
                $latitude = $params['order_lat'];
                $longitude = $params['order_lng'];
                updatelocationattendance($attendance_id,$latitude,$longitude,$params['store_id']);
            }            
            $result = $this->orderRepository->placeOrder($params);

           if (isset($result['error']) && $result['error'] === true) {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }  
            return response()->json(
                [
                    'data' => $result
                ],
                Response::HTTP_CREATED
            );
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()]);
        }
        
        
    }


    // public function list(Request $request)
    // {
    //   $from_date = !empty($request->from_date)?$request->from_date:date('Y-m-d', strtotime("-15 days"));
    //     $to_date = !empty($request->to_date)?$request->to_date:date('Y-m-d');
    //     $take = !empty($request->take)?$request->take:10;
    //     $page = isset($request->page)?$request->page:0;
    //     $skip = ($take*$page);

    //   $order = Order::select('id','store_id','amount','order_no','created_at')->with('stores:id,store_name,bussiness_name')->with('orderProducts:id,order_id,product_id,product_name,qty,pcs,piece_price,price')->with('packingslip:id,order_id,is_disbursed')->where('status', '!=', 3)->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date])->orderBy('id','desc')->skip($skip)->take($take)->get();

    //     $count_order = Order::where('status', '!=', 3)->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date])->count();
    //     $total_amount = Order::where('status', '!=', 3)->whereBetween(DB::raw('DATE(created_at)'), [$from_date,$to_date])->sum('amount');

    //     $isPrev = 0;
    //     $isNext = 0;

    //     if($page == 0){
    //         if($count_order > $take){
    //             $isNext = 1;
    //         }
    //     } else {
    //         if($page > 0){
    //             $isPrev = 1;
    //             $page = ($page + 1);
    //             $skips = ($take * $page);
    //             // echo $skips; die;
    //             if($skips < $count_order){
    //                 $isNext = 1;
    //             } 
    //         }
    //     }


    //     return response()->json([
    //         'error' => false,
    //         'resp' => "All Order List",
    //         'data' => array(
    //             'from_date' => $from_date,
    //             'to_date' => $to_date,
    //             'count_order' => $count_order,
    //             'isPrev' => $isPrev,
    //             'isNext' => $isNext,
    //             'total_amount' => $total_amount,
    //             'order' => $order
    //         )
    //     ]);

    // }
    
     public function list(Request $request)
{
     if (empty($request->user_id)) {
        return response()->json([
            'error' => true,
            'message' => 'user_id is required'
        ], 400);
    }

    $user = User::find($request->user_id);

    if (!$user) {
        return response()->json([
            'error' => true,
            'message' => 'User not found'
        ], 404);
    }
    
    // ---------- Filters ----------
    $from_date = $request->from_date ?? date('Y-m-d', strtotime('-15 days'));
    $to_date   = $request->to_date   ?? date('Y-m-d');
    $take      = $request->take      ?? 10;
    $page      = $request->page      ?? 0;
    $skip      = $take * $page;

    // ---------- Orders Query ----------
    $orders = Order::select('id', 'status','store_id', 'amount', 'order_no', 'created_at')
        ->with('stores:id,store_name,bussiness_name')
        ->with('orderProducts:id,order_id,product_id,product_name,qty,pcs,piece_price,price')
        ->with(['packingslip' => function ($q) {
            // $q->whereNull('invoice_id')   // ❗ invoice not created
              $q->where('is_disbursed', 1) // ❗ ready / pending delivery
               ->select(
                    'id',
                    'order_id',
                    'slipno',
                    'store_id',
                    'is_disbursed',
                    'invoice_id',
                    'created_at'
                );
        }])
        ->where('status', '!=', 3)
         ->where('user_id', $user->id) 
        ->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date])
        ->orderBy('id', 'desc')
        ->skip($skip)
        ->take($take)
        ->get();

    // ---------- Counts ----------
    $count_order = Order::where('status', '!=', 3)
        ->where('user_id', $user->id) 
        ->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date])
        ->count();

    $total_amount = Order::where('status', '!=', 3)
        ->where('user_id', $user->id) 
        ->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date])
        ->sum('amount');

    // ---------- Pagination Flags ----------
    $isPrev = $page > 0 ? 1 : 0;
    $isNext = ($skip + $take) < $count_order ? 1 : 0;

    // ---------- Transform Response ----------
    $orders = $orders->map(function ($order) {
         $statusMap = [
            1 => 'Received',
            2 => 'Pending',
            3 => 'Cancelled',
            4 => 'Completed',
            5 => 'In Progress'
        ];
         $orderStatus = null;
        $packingType = null;
    
        if ($order->packingslip) {
    
            // 🟡 CASE 1: Invoice NOT generated (fetch FIRST logically)
            if ($order->packingslip->invoice_id === null) {
                $packingType = 'pending_invoice';
            }
    
            // 🟢 CASE 2: Invoice generated → show status
            if ($order->packingslip->invoice_id !== null) {
                $packingType = 'invoice_generated';
                $orderStatus = $statusMap[$order->status] ?? 'Unknown';
            }
        }
        return [
            'id' => $order->id,
            'store_id' => $order->store_id,
            'amount' => $order->amount,
            'order_no' => $order->order_no,
            'created_at' => $order->created_at,
            'status' => $order->status,
            'store' => $order->stores,
            'order_products' => $order->orderProducts,

            //  Show ONLY when invoice_id NULL & is_disbursed = 1
            'packingslip' => $order->packingslip ? [
                'packingslip_id' => $order->packingslip->id,
                'slipno'         => $order->packingslip->slipno,
                'store_id'       => $order->packingslip->store_id,
                'is_disbursed'   => $order->packingslip->is_disbursed,
                'invoice_id'     => $order->packingslip->invoice_id,
                'created_at'     => $order->packingslip->created_at,
            ] : null,
           
            // //Order Status For Mobile App
             'order_status' => $orderStatus
        ];
    });

    // ---------- Final Response ----------
    return response()->json([
        'error' => false,
        'resp'  => 'All Order List',
        'data'  => [
            'user_id'      => $user->id,
            'from_date'    => $from_date,
            'to_date'      => $to_date,
            'count_order'  => $count_order,
            'isPrev'       => $isPrev,
            'isNext'       => $isNext,
            'total_amount' => $total_amount,
            'order'        => $orders,
        ]
    ]);
}



       
}
