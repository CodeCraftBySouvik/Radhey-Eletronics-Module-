<?php

namespace App\Repositories;

use App\Interfaces\OrderInterface;
use App\Models\Order;

use App\Traits\UploadAble;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\OrderProduct;
use App\Models\PaymentCollection;
use App\Models\Ledger;
use App\Models\Store;
use App\StoreVisit;
use Illuminate\Support\Facades\Log;

class OrderRepository implements OrderInterface
{
    use UploadAble;

    public function __construct() {
        // $this->ip = $_SERVER['REMOTE_ADDR'];
    }

    public function listAll()
    {
	
        return Order::latest('id')->get();
    }

    public function listById($id)
    {
        return Order::findOrFail($id);
    }
    public function listByuserId($userId)
    {
        return Order::where('user_id',$userId)->get();
    }

    public function listByStoreId($storeId)
    {
        return Order::where('store_id',$storeId)->get();
    }
    

    public function listByStatus($status)
    {
	
        return Order::latest('id')->where('status', $status)->get();
    }

    public function searchOrder(string $term)
    {
        return Order::where([['fname', 'LIKE', '%' . $term . '%']])->get();
    }
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            $collectedData = collect($data);
            $newEntry = new Order;
            $newEntry->ip = $collectedData['ip'];

            $newEntry->user_id = $collectedData['user_id'];
            $newEntry->store_id = $collectedData['store_id'];
            $newEntry->fname = $collectedData['fname'];
            $newEntry->lname = $collectedData['lname'];
            $newEntry->email = $collectedData['email'];
            $newEntry->mobile = $collectedData['mobile'];
            $newEntry->alt_mobile = $collectedData['alt_mobile'];

            $newEntry->billing_address = $collectedData['billing_address'];
            $newEntry->billing_landmark = $collectedData['billing_landmark'];
            $newEntry->billing_country = $collectedData['billing_country'];
            $newEntry->billing_state = $collectedData['billing_state'];
            $newEntry->billing_city = $collectedData['billing_city'];
            $newEntry->billing_pin = $collectedData['billing_pin'];

            $newEntry->shipping_address = $collectedData['shipping_address'];
            $newEntry->shipping_landmark = $collectedData['shipping_landmark'];
            $newEntry->shipping_country = $collectedData['shipping_country'];
            $newEntry->shipping_state = $collectedData['shipping_state'];
            $newEntry->shipping_city = $collectedData['shipping_city'];
            $newEntry->shipping_pin = $collectedData['shipping_pin'];

            $newEntry->amount = $collectedData['amount'];
            $newEntry->tax_amount = $collectedData['tax_amount'];
            $newEntry->discount_amount = $collectedData['discount_amount'];
            $newEntry->final_amount = $collectedData['final_amount'];
            $newEntry->gst_no = $collectedData['gst_no'];

            DB::commit();
            return $newEntry;
        } catch (\Throwable $th) {
            // throw $th;
            DB::rollback();
        }
    }

    public function update($id, array $newDetails)
    {
        DB::beginTransaction();

        try {
            $updatedEntry = Order::findOrFail($id);
            $collectedData = collect($newDetails);

            $updatedEntry->user_id = $collectedData['user_id'];
            $updatedEntry->fname = $collectedData['fname'];
            $updatedEntry->lname = $collectedData['lname'];
            $updatedEntry->email = $collectedData['email'];
            $updatedEntry->mobile = $collectedData['mobile'];
            $updatedEntry->alt_mobile = $collectedData['alt_mobile'];

            $updatedEntry->billing_address_id = $collectedData['billing_address_id'];
            $updatedEntry->billing_address = $collectedData['billing_address'];
            $updatedEntry->billing_landmark = $collectedData['billing_landmark'];
            $updatedEntry->billing_country = $collectedData['billing_country'];
            $updatedEntry->billing_state = $collectedData['billing_state'];
            $updatedEntry->billing_city = $collectedData['billing_city'];
            $updatedEntry->billing_pin = $collectedData['billing_pin'];

            $updatedEntry->shipping_address_id = $collectedData['shipping_address_id'];
            $updatedEntry->shipping_address = $collectedData['shipping_address'];
            $updatedEntry->shipping_landmark = $collectedData['shipping_landmark'];
            $updatedEntry->shipping_country = $collectedData['shipping_country'];
            $updatedEntry->shipping_state = $collectedData['shipping_state'];
            $updatedEntry->shipping_city = $collectedData['shipping_city'];
            $updatedEntry->shipping_pin = $collectedData['shipping_pin'];

            $updatedEntry->amount = $collectedData['amount'];
            $updatedEntry->tax_amount = $collectedData['tax_amount'];
            $updatedEntry->discount_amount = $collectedData['discount_amount'];
            $updatedEntry->coupon_code_id = $collectedData['coupon_code_id'];
            $updatedEntry->final_amount = $collectedData['final_amount'];
            $updatedEntry->gst_no = $collectedData['gst_no'];

            DB::commit();
            return $updatedEntry;
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
        }
    }

    public function toggle($id, $status)
    {
        $updatedEntry = Order::findOrFail($id);
        $updatedEntry->status = $status;
        $updatedEntry->save();

        /* If order make cancel save order products with rest quantity */

        if($status == 3){
            $order_products = DB::table('order_products')->where('order_id',$id)->get();
            if(!empty($order_products)){
                foreach($order_products as $op){
                    DB::table('order_cancelled_products')->insert([
                        'order_id' => $id,
                        'product_id' => $op->product_id,
                        'qty' => ($op->qty - $op->release_qty)
                    ]);
                }
            }
            
        }

        return $updatedEntry;
    }

    // public function delete($id)
    // {
    //     Order::destroy($id);
    // }

    public function listByorderId($id)
    {
       return Order::with('orderProducts')->with('users')->with('stores')->where('id',$id)->get();
    }
    public function storeFilter($id)
    {
       return Order::with('users')->with('stores')->where('store_id',$id)->get();
    }

       public function getStoreCreditStatus(int $storeId)
    {
        $store = Store::find($storeId);
        if (!$store) {
            return ['outstanding' => 0, 'due_days' => 0];
        }

        $ledgerAmount = getStoreLedgerAmount($storeId);
        $outstanding = replaceMinusSign($ledgerAmount['outstanding']);

        if ($outstanding <= 0) {
            return [
                'outstanding' => 0,
                'due_days' => 0
            ];
        }

        $total_payment = PaymentCollection::where('store_id', $storeId)
            ->sum('collection_amount');

        $invoice_date = Ledger::where('store_id', $storeId)
            ->where('is_debit', 1)
            ->whereRaw('transaction_amount > ?', [$total_payment])
            ->orderBy('entry_date', 'asc')
            ->value('entry_date');

        $due_days = $invoice_date
            ? now()->diffInDays($invoice_date)
            : 0;

        return [
            'outstanding' => $outstanding,
            'due_days' => $due_days,
            'invoice_date' => $invoice_date
        ];
    }


    public function placeOrder(array $data){
       

        $collectedData = collect($data);

        DB::beginTransaction();

        try {
            // 1 order
            $order_no = "VZ".mt_rand();
            $newEntry = new Order;
            $newEntry->order_no = $order_no;
            $newEntry->user_id = $collectedData['user_id'];
            $newEntry->store_id = $collectedData['store_id'];
            $newEntry->comment = !empty($collectedData['comment'])?$collectedData['comment']:'';
            $newEntry->order_location = !empty($collectedData['order_location'])?$collectedData['order_location']:'';
            $newEntry->order_lat = !empty($collectedData['order_lat'])?$collectedData['order_lat']:'';
            $newEntry->order_lng = !empty($collectedData['order_lng'])?$collectedData['order_lng']:'';

            // fetch cart details
            $existingCart = Cart::where('user_id', $newEntry->user_id);
            $cartData = $existingCart->get();

            // dd($cartData);
            $subtotal = 0;
            foreach($cartData as $cartValue) {
                $subtotal += $cartValue->price * $cartValue->qty;
            }
            // ================= CREDIT CHECK START =================

            $storeId = $collectedData['store_id'];

            // get due info (same logic as store due report)
            $creditInfo = $this->getStoreCreditStatus($storeId);
            // fetch store credit config
            $store = Store::find($storeId);

            // 1️⃣ Credit limit exceeded
            if (
                !empty($store->credit_limit) &&
                ($creditInfo['outstanding'] + $subtotal) > $store->credit_limit
            ) {
                DB::rollBack();
                return [
                    'error' => true,
                    'message' => 'Order blocked: Credit limit exceeded'
                ];
            }

            // 2️⃣ Due days exceeded & outstanding exists
            $allowedDays = $store->credit_days ?? 30;

            if (
                $creditInfo['due_days'] > $allowedDays &&
                $creditInfo['outstanding'] > 0
            ) {
                DB::rollBack();
                return [
                    'error' => true,
                    'message' => 'Order blocked: Payment overdue beyond allowed period'
                ];
            }

            // ================= CREDIT CHECK END =================

            $newEntry->amount = $subtotal;
            $newEntry->final_amount = $subtotal;
            $newEntry->signature = !empty($collectedData['signature'])?$collectedData['signature']:'';
            $newEntry->is_gst = $collectedData['is_gst'];
            $newEntry->created_at = date('Y-m-d H:i:s');
            $newEntry->updated_at = date('Y-m-d H:i:s');
            // dd($newEntry);
            $newEntry->save();
            
            // 2 insert cart data into order products
            $orderProducts = [];
            foreach($cartData as $cartValue) {
                $orderProducts[] = [
                    'order_id' => $newEntry->id,
                    'product_id' => $cartValue->product_id,
                    'product_name' => $cartValue->product_name,
                    'product_image' => $cartValue->product_image,                    
                    'price' => $cartValue->price,
                    'piece_price' => $cartValue->piece_price,
                    'qty' => $cartValue->qty,
                    'pcs' => $cartValue->pcs,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                Cart::destroy($cartValue->id);
            }
            $orderProductsNewEntry = OrderProduct::insert($orderProducts);
            DB::commit();

            return $newEntry;
        } catch (\Throwable $th) {
            throw $th;
            // dd($th);
            DB::rollback();
            return false;
        }
    }

    public function placeOrderUpdated(array $data){
        $collectedData = collect($data);
        
        // echo "<pre>";
        // print_r($collectedData);
        // die();

        DB::beginTransaction();

        // return $collectedData['email'] ?? 0;

        try {
        //     echo "<pre>";
        // print_r($collectedData);
        // die();
            // 1 order
            $order_no = "ONN".mt_rand();
            $newEntry = new Order;
            $newEntry->order_no = $order_no;
            $newEntry->user_id = $collectedData['user_id'];
            $newEntry->ip = $this->ip;
            $newEntry->store_id = $collectedData['store_id'];
            $newEntry->order_type = $collectedData['order_type'] ?? null;
            $newEntry->order_lat = $collectedData['order_lat'] ?? null;
            $newEntry->order_lng = $collectedData['order_lng'] ?? null;
            $newEntry->email = $collectedData['email'] ?? null;
            $newEntry->mobile = $collectedData['mobile'] ?? null;
            $newEntry->fname = $collectedData['fname'] ?? null;
            $newEntry->lname = $collectedData['lname'] ?? null;
            $newEntry->billing_country = $collectedData['billing_country'] ?? null;
            $newEntry->billing_address = $collectedData['billing_address'] ?? null;
            $newEntry->billing_landmark = $collectedData['billing_landmark'] ?? null;
            $newEntry->billing_city = $collectedData['billing_city'] ?? null;
            $newEntry->billing_state = $collectedData['billing_state'] ?? null;
            $newEntry->billing_pin = $collectedData['billing_pin'] ?? null;

            // shipping & billing address check
            $shippingSameAsBilling = $collectedData['shippingSameAsBilling'] ?? 0;
            $newEntry->shippingSameAsBilling = $shippingSameAsBilling;

            // dd($shippingSameAsBilling);

            if ($shippingSameAsBilling == 0) {
                $newEntry->shipping_country = $collectedData['shipping_country'] ?? null;
                $newEntry->shipping_address = $collectedData['shipping_address'] ?? null;
                $newEntry->shipping_landmark = $collectedData['shipping_landmark'] ?? null;
                $newEntry->shipping_city = $collectedData['shipping_city'] ?? null;
                $newEntry->shipping_state = $collectedData['shipping_state'] ?? null;
                $newEntry->shipping_pin = $collectedData['shipping_pin'] ?? null;
            } else {
                $newEntry->shipping_country = $collectedData['billing_country'] ?? null;
                $newEntry->shipping_address = $collectedData['billing_address'] ?? null;
                $newEntry->shipping_landmark = $collectedData['billing_landmark'] ?? null;
                $newEntry->shipping_city = $collectedData['billing_city'] ?? null;
                $newEntry->shipping_state = $collectedData['billing_state'] ?? null;
                $newEntry->shipping_pin = $collectedData['billing_pin'] ?? null;
            }

            $newEntry->shipping_method = $collectedData['shipping_method'] ?? null;

            // fetch cart details
            $cartData = Cart::where('user_id', $newEntry->user_id)->get();
            $subtotal = 0;
            foreach($cartData as $cartValue) {
                $subtotal += $cartValue->offer_price * $cartValue->qty;
                $store_id = $cartValue->store_id;
                $order_type = $cartValue->order_type;
            }
            $newEntry->amount = $subtotal;
            $newEntry->shipping_charges = $collectedData['shipping_charges'] ?? null;
            $newEntry->tax_amount = $collectedData['tax_amount'] ?? null;
            $newEntry->comment = $collectedData['comment'] ?? null;
            // $newEntry->discount_amount = 0;
            // $newEntry->coupon_code_id = 0;
            $total = (int) $subtotal +$newEntry->shipping_charges + $newEntry->tax_amount ;
            $newEntry->final_amount = $total;
            //$newEntry->store_id = $store_id;
            //$newEntry->order_type = $order_type;
            // $coupon_code_id = $cartData[0]->coupon_code_id ?? 0;
            // $newEntry->coupon_code_id = $coupon_code_id;
            $newEntry->save();


            // 2 insert cart data into order products
            $orderProducts = [];
            foreach($cartData as $cartValue) {
                $orderProducts[] = [
                    'order_id' => $newEntry->id,
                    'product_id' => $cartValue->product_id,
                    'product_name' => $cartValue->product_name,
                    'product_image' => $cartValue->product_image,
                    'product_slug' => $cartValue->product_slug,
                    'product_variation_id' => $cartValue->product_variation_id,
                    'price' => $cartValue->price,
                    'offer_price' => $cartValue->offer_price,
                    'color' => $cartValue->color,
                    'size' => $cartValue->size,
                    'qty' => $cartValue->qty,
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
            $orderProductsNewEntry = OrderProduct::insert($orderProducts);

            Cart::where('user_id', $newEntry->user_id)->delete();

            DB::commit();
            return $order_no;
        } catch (\Throwable $th) {
            throw $th;
            // dd($th);
            DB::rollback();
            return $th;
        }
    }

    public function Totalsales($storeId){
        return Order::where('store_id', $storeId)->sum('final_amount');
    }

    public function latestsales($storeId){
        return Order::where('store_id',$storeId)->where('created_at', '>', \Carbon\Carbon::now()->startOfMonth()->subMonth(3))->latest('id', 'desc')->sum('final_amount');
    }

    public function lastOrder($storeId){
        return Order::select('final_amount')->where('store_id',$storeId)->latest('id','desc')->first();
    }

    public function avgOrder($storeId){
        $resp = Order::where('store_id',$storeId)->avg('final_amount');
        if ($resp) {
            return (int)$resp;
        } else {
            return null;
        }
    }

    public function lastVisit($storeId){
        $data = StoreVisit::where('store_id',$storeId)->where('created_at', '>', \Carbon\Carbon::now()->startOfDay())->latest('id', 'desc')->get();

        if ($data) {
            return $data;
        } else {
            return null;
        }
    }

    public function Totalamount($storeId,$productId){
        return OrderProduct::where('store_id', $storeId)->whereIn('product_id', $productId)->with('orders')->sum('final_amount');
    }

    public function TotalOrder($storeId,$productId){
        $purchases = OrderProduct::where('store_id', $storeId)->whereIn('product_id', $productId)->withCount('orders')->get();
    }


}
