<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\CartInterface;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ThresholdRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function __construct(CartInterface $CartRepository)
    {
        $this->CartRepository = $CartRepository;
    }

    public function show($userId): JsonResponse
    {

        $cart = $this->CartRepository->listById($userId);

        return response()->json(['error'=>false, 'resp'=>'Cart data fetched successfully','data'=>$cart]);
    }

    public function simpleBulkAddcart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
            'store_id' => ['required', 'integer', 'min:1', 'exists:stores,id'],
            // 'device_id' => ['nullable'],
            'product_id' => ['required', 'integer','exists:products,id'],            
            'price' => ['required'],
            'qty' => ['required']           
        ]);

        if (!$validator->fails()) {
            $params = $request->except('_token');
            $price = $params['price'];  
            $product = DB::table('products')->find($params['product_id']);
            $threshold_price = $product->threshold_price;
            $sell_price = $product->sell_price; # each product sell 
            $pcs = $product->pcs; # last PO pieces

            if($sell_price == 0){
                // die('No purchase order added for this product');
                return response()->json(['error' => true, 'resp' => 'No sell price is found for this product' ]);
            } else {
                $eachProductPrice = $price / $pcs;
                // echo "each sell price:- ".$sell_price."<br/>";                
                // echo "each carton price respect of current ".$pcs." pieces:- ".$eachctnprice."<br/>"; 
                // echo "each product price:- ".$eachProductPrice."<br/>";
                // die;
			
				
                if($eachProductPrice >= $sell_price){
                    
                    $data = $this->CartRepository->simpleBulkAddcart($params);

                    if ($data) {
                        return response()->json(['error' => false, 'resp' => 'Product successfully added to cart', 'data' => $data]);
                    } else {
                        return response()->json(['error' => true, 'resp' => 'Something happened']);
                    }

                    
                } else {
                    // die('Allowed');                    
                    return response()->json(['error' => true, 'resp' => 'Product sell price is Rs. '.$sell_price. '. Please increase your amount for this product and carton quantity. No of product pieces '.$pcs.' ' , 'data' => array() ]);
                }
            }

            

            
        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
    }

    public function delete(Request $request, $id)
    {
        $data = $this->CartRepository->delete($id);

        if ($data) {
            return response()->json(['error' => false, 'resp' => 'Product removed from cart']);
            
        } else {
            return response()->json(['error' => true, 'resp' => 'Cart not found']);
            # code...
        }
       
    }

    public function clear(Request $request,$userId)
    {
        if(!empty($userId)){
            $data = Cart::where('user_id',$userId)->delete();
            
            return Response::json([
                'error' => false,
                'resp' => "Cart cleared successfully"
            ]);

        } else {
            return Response::json([
                'error' => true,
                'resp' => "Please specify user"
            ]);
        }
        
    }

    public function addPriceRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [            
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
            'store_id' => ['required', 'integer', 'min:1', 'exists:stores,id'],   
            'product_id' => ['required', 'integer', 'min:1', 'exists:products,id'],            
            'price' => ['required'],
            'qty' => ['required', 'integer']
        ]);


        if (!$validator->fails()) {

            $checkExistProductCart = Cart::where('user_id',$request->user_id)->where('store_id',$request->store_id)->where('product_id',$request->product_id)->first();

            if(!empty($checkExistProductCart)){
                return response()->json(['error'=>true,'message'=>"This product already added in cart.", 'data' => (object) array() ],200);
            }

            $product = Product::find($request->product_id);
            $threshold_price = $product->threshold_price;
            $sell_price = $product->sell_price;
            $pcs = $product->pcs;

            if($sell_price == 0){
                return response()->json(['error'=>true,'message'=>"No sell price is found for this product", 'data' => (object) array() ],200);
            } else {
                $price = $request->price;
                $eachProductPrice = $price / $pcs;
                if($eachProductPrice < $sell_price){
                    $checkRequestExists = DB::table('product_threshold_request')->where('user_id',$request->user_id)->where('store_id',$request->store_id)->where('product_id',$request->product_id)->where('is_approved',0)->first();
    
                    if(!empty($checkRequestExists)){
                        return response()->json([
                            'error' => true, 'status' => 200, 'message' => "You have a pending price request sent for this product of same store", 'data' => (object) []
                        ],200);
                    }

                    $unique_id = genAutoIncreNoYearWise('product_threshold_request',date('Y'),5);
                    $unique_id = "THRESH".$unique_id;                                       
            
                    $id = ThresholdRequest::insertGetId([
                        'unique_id' => $unique_id,
                        'user_id' => $request->user_id,
                        'store_id' => $request->store_id,
                        'product_id' => $request->product_id,
                        'price' => $price,
                        'threshold_price' => $threshold_price,
                        'sell_price' => $sell_price,
                        'pcs' => $pcs,
                        'qty' => $request->qty,
                        'started_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
            
                    $product_threshold_request = ThresholdRequest::find($id);
            
                    
                    return response()->json([
                            'error' => false,
                            'status' => 200,
                            'message' => "Price request sent successfully",
                            'data' => $product_threshold_request
                        ],200);
    
    
    
                } else {
                    return response()->json(['error'=>true, 'message' => "No need to send request for this product with this price. Please add the product to cart directly", 'data' => (object)['sell_price'=>$sell_price,'pcs'=>$pcs] ]);
                }
            }

            


        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }

    }

    public function requestedProducts(Request $request)
    {
        # code...

        $validator = Validator::make($request->all(), [            
            'user_id' => ['required', 'integer', 'min:1', 'exists:users,id'],
            'store_id' => ['required', 'integer', 'min:1', 'exists:stores,id']            
        ]);

        if (!$validator->fails()) {
            $params = $request->except('_token');
            $data = DB::table('product_threshold_request AS ptr')->select('ptr.product_id','ptr.sell_price','ptr.pcs','ptr.price','ptr.qty','ptr.is_approved','ptr.customer_approval','p.name AS pro_name','p.image AS pro_image')->leftJoin('products AS p','p.id','ptr.product_id')->where('ptr.user_id',$params['user_id'])->where('ptr.store_id',$params['store_id'])->where('ptr.customer_approval', 0)->get();

            // $data = ThresholdRequest::select('product_id','sell_price','pcs','qty','is_approved','customer_approval')->with('product:id,name,image')->where('store_id', $params['store_id'])->where('customer_approval', 0)->get()->toArray();

            return response()->json([
                'error' => false,
                'status' => 200,
                'message' => "Requested products.",
                'data' => $data
            ],200);


        } else {
            return response()->json(['status' => 400, 'message' => $validator->errors()->first()],400);
        }
    }
	

   

}
