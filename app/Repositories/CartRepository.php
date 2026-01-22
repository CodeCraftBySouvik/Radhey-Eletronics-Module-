<?php

namespace App\Repositories;

use App\Interfaces\CartInterface;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\ProductColorSize;
use App\Models\ProductImage;
use App\Models\Product;
use App\Traits\UploadAble;
use Illuminate\Support\Facades\Auth;

class CartRepository implements CartInterface
{
    public function __construct() {
        // $this->ip = $_SERVER['REMOTE_ADDR'];
    }



    use UploadAble;
    /**
     * This method is for show cart list
     *
     */
    public function listAll()
    {
        return Cart::all();
    }



    /**
     * This method is for show user cart details
     * @param  $id
     *
     */
    public function listById($userId)
    {

       return Cart::where('user_id',$userId)->get();
    }
    

    public function simpleBulkAddcart(array $data)
    {
        $collectedData = collect($data);        
        $cartExistsUser = Cart::where('user_id', $collectedData['user_id'])->where('store_id', $collectedData['store_id'])->where('product_id', $collectedData['product_id'])->first();

        if ($cartExistsUser) {
            // $cartExistsUser->qty = $cartExistsUser->qty + $collectedData['qty'];
            $cartExistsUser->qty = $collectedData['qty'];
            $cartExistsUser->price = $collectedData['price'];
            $cartExistsUser->save();
            return true;
        } else {
            $productData = Product::find($collectedData['product_id']);
            // $product_name = $productData->name;
            // $product_image = $productData->image;
            // dd($product_image);
            $newEntry = new Cart;
            $newEntry->user_id = $collectedData['user_id'];    
            $newEntry->store_id = $collectedData['store_id'];       
            $newEntry->product_id = $collectedData['product_id'];    
            $newEntry->product_name =  $productData->name;
            $newEntry->product_image =  $productData->image;       
            $newEntry->price = $collectedData['price'];   
            $newEntry->piece_price = ($collectedData['price'] / $productData->pcs);   
            $newEntry->qty = $collectedData['qty'];
            $newEntry->pcs = ($productData->pcs * $collectedData['qty']);
            $newEntry->created_at = date('Y-m-d H:i:s');
            $newEntry->save();
            return true;
        }
        
    }

    public function delete($id)
    {
        // $data = Cart::destroy($id);
        $data = Cart::find($id);
        if ($data) {
            $data = $data->delete();
        }
        return $data;
       
    }




}

