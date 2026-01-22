<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Models\StockBox;
use App\Models\StockProduct;
use App\Models\StockLog;
use App\Models\PackingslipNew1;
use App\Models\Packingslip;
use App\Models\PurchaseReturnOrder;
use App\Models\PurchaseReturnProduct;
use App\Models\PurchaseReturnBox;
use App\Models\Ledger;
use App\Models\Journal;

class ScanController extends Controller
{
    //
    /*public function box(Request $request): JsonResponse
    {
        
        $validator = Validator::make($request->all(), [
            'barcode_no' => ['required'],
            'scanned_weight_val' => ['required']
        ]);

        $params = $request->except('_token');

        if (!$validator->fails()) {

            $barcode_no = $params['barcode_no'];
            $scanned_weight_val = $params['scanned_weight_val'];

            $checkBarcode = DB::table('purchase_order_boxes')->where('barcode_no',$barcode_no)->first();

            if(!empty($checkBarcode)){
                if(empty($checkBarcode->is_scanned)){
                    $po_weight_val = $checkBarcode->po_weight_val;
                    $approxUpWeight = ($po_weight_val + 50);
                    $approxDownWeight = ($po_weight_val - 50);
                    
                    if (($scanned_weight_val <= $approxUpWeight && $scanned_weight_val >= $approxDownWeight) ) {
                        // die('Ok');
                        DB::table('purchase_order_boxes')->where('barcode_no',$barcode_no)->update(['is_scanned'=>1, 'scanned_weight_val' => $scanned_weight_val, 'updated_at'=>date('Y-m-d H:i:s')]);

                        return response()->json(
                            [
                                'error' => false,
                                'status' => 200,
                                'message' => 'Scanned successfully',
                                'data' => (object)[]
                            ]
                        );
                    } else {
                        // die('Mismatched');
                        return response()->json(
                            [
                                'error' => true,
                                'status' => 200,
                                'message' => 'Mismatched weight.',
                                'data' => (object)[]
                            ]
                        );
                    }
                }else{
                    return response()->json(
                        [
                            'error' => true,
                            'status' => 200,
                            'message' => 'Already scanned',
                            'data' => (object)[]
                        ]
                    );
                }
            }else{
                return response()->json(
                    [
                        'error' => true,
                        'status' => 200,
                        'message' => 'No barcode found',
                        'data' => (object)[]
                    ]
                );
            }
            
        } else {
            return response()->json(
                [
                    'status' => 400, 
                    'message' => $validator->errors()->first()
                ]
            );
        }
        
    }*/

     public function box(Request $request)
    {
        # without weight value...
        $validator = Validator::make($request->all(), [
            'barcode_no' => ['required']
        ]);

        $params = $request->except('_token');

        if (!$validator->fails()) {

            $barcode_no = $params['barcode_no'];
            $checkBarcode = DB::table('purchase_order_boxes')->where('barcode_no',$barcode_no)->first();
            if(!empty($checkBarcode)){
                $purchase_order_id = $checkBarcode->purchase_order_id;
                $checkPO = DB::table('purchase_orders')->find($purchase_order_id);
                // dd($purchase_order_id);
                if($checkPO->goods_in_type == 'bulk'){
                    return response()->json(
                        [
                            'error' => true,
                            'status' => 200,
                            'message' => 'This barcode is under bulk goods in',
                            'data' => (object)['PCS'=>$checkBarcode->pcs]
                        ]
                    );
                }
                if(empty($checkBarcode->is_scanned)){
                    $po_weight_val = $checkBarcode->po_weight_val;
                      
                    DB::table('purchase_order_boxes')->where('barcode_no',$barcode_no)->update(['is_scanned'=>1, 'scanned_weight_val' => $po_weight_val, 'updated_at'=>date('Y-m-d H:i:s')]);

                    return response()->json(
                        [
                            'error' => false,
                            'status' => 200,
                            'message' => 'Scanned successfully',
                            'data' => (object)['PCS'=>$checkBarcode->pcs]
                        ]
                    );
                    
                }else{
                    return response()->json(
                        [
                            'error' => true,
                            'status' => 200,
                            'message' => 'Already scanned',
                            'data' => (object)['PCS'=>$checkBarcode->pcs]
                        ]
                    );
                }
            }else{
                return response()->json(
                    [
                        'error' => true,
                        'status' => 200,
                        'message' => 'No barcode found',
                        'data' => (object)[]
                    ]
                );
            }
            
        } else {
            return response()->json(
                [
                    'status' => 400, 
                    'message' => $validator->errors()->first()
                ]
            );
        }
    }
    /*public function stockout(Request $request): JsonResponse
    {
        
        $validator = Validator::make($request->all(), [
            'barcode_no' => ['required'],
            'stock_out_weight_val' => ['required']
        ]);

        $params = $request->except('_token');

        if (!$validator->fails()) {

            $barcode_no = $params['barcode_no'];
            $stock_out_weight_val = $params['stock_out_weight_val'];

            $checkBarcode = DB::table('stock_boxes')->where('barcode_no',$barcode_no)->first();

            if(!empty($checkBarcode)){
                if(!empty($checkBarcode->is_scanned) && empty($checkBarcode->scan_no)){

                    $stock_in_weight_val = $checkBarcode->stock_in_weight_val;
                    $approxUpWeight = ($stock_in_weight_val + 50);
                    $approxDownWeight = ($stock_in_weight_val - 50);

                    if (($stock_out_weight_val <= $approxUpWeight && $stock_out_weight_val >= $approxDownWeight) ) {

                        DB::table('stock_boxes')->where('barcode_no',$barcode_no)->update(['scan_no'=>$barcode_no, 'stock_out_weight_val' => $stock_out_weight_val, 'updated_at'=>date('Y-m-d H:i:s')]);

                        DB::table('packing_slip_boxes')->where('barcode_no',$barcode_no)->update(['is_disbursed' => 1]);

                        return response()->json(
                            [
                                'error' => false,
                                'status' => 200,
                                'message' => 'Scanned successfully',
                                'data' => (object)[]
                            ]
                        );

                    } else {
                        return response()->json(
                            [
                                'error' => true,
                                'status' => 200,
                                'message' => 'Mismatched weight.',
                                'data' => (object)[]
                            ]
                        );
                    }

                }else{
                    return response()->json(
                        [
                            'error' => true,
                            'status' => 200,
                            'message' => 'Not released from inventory',
                            'data' => (object)[]
                        ]
                    );
                }
            }else{
                return response()->json(
                    [
                        'error' => true,
                        'status' => 200,
                        'message' => 'No barcode found',
                        'data' => (object)[]
                    ]
                );
            }
            
        } else {
            return response()->json(
                [
                    'status' => 400, 
                    'message' => $validator->errors()->first()
                ]
            );
        }
        
    }*/

   public function stockout(Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'slip_no' => 'required|exists:packingslips,slipno',
            'product_id' => 'required|exists:products,id',
            'barcode_no' => 'required|exists:stock_boxes,barcode_no',
            // 'stock_out_weight_val' => 'required'
        ]);

        $params = $request->except('_token');

        if(!$validator->fails()){
            $exist = StockBox::where('barcode_no',$params['barcode_no'])->first();
            if(empty($exist->is_scanned)){
                if($exist->product_id == $params['product_id']){
                    $packingslips = PackingslipNew1::where('slipno',$params['slip_no'])->first();
                    $packingslip_id = $packingslips->id;
                    
                    $packing_slip = Packingslip::where('slip_no',$params['slip_no'])->where('product_id',$params['product_id'])->first();
                    $scan_product_quantity = $packing_slip->quantity;
                    
                    $check_done = StockBox::where('slip_no',$params['slip_no'])->where('product_id',$params['product_id'])->count();

                    $stock_in_weight_val = $exist->stock_in_weight_val;

                    if($check_done == $scan_product_quantity){
                        return Response::json(
                            [
                                'error' => true,
                                'message' => "For this product ".$scan_product_quantity." barcodes scanning completed ",
                                'data' => (object)['PCS'=>$exist->pcs]
                            ],200
                        );
                    } else {
                        StockBox::where('barcode_no',$params['barcode_no'])->update([
                            'scan_no'=>$params['barcode_no'], 
                            'is_scanned'=>1,
                            'stock_out_weight_val' => $stock_in_weight_val,
                            'packingslip_id' => $packingslip_id,
                            'slip_no' => $params['slip_no'],
                            'updated_at'=>date('Y-m-d H:i:s')
                        ]);
                        $count_product_scanned = StockBox::where('packingslip_id',$packingslip_id)->where('product_id',$params['product_id'])->where('is_scanned',1)->count();
                        return Response::json(['error' => false, 'message' => "Scanned successfully", 'data' => array(
                            'required_product_scan' => $scan_product_quantity,
                            'count_product_scanned' => $count_product_scanned,
                            'PCS'=>$exist->pcs,
                            'else_product_scan' => ($scan_product_quantity - $count_product_scanned)
                        ) ],200);
                    }
                } else {
                    return Response::json(
                        [
                            'error' => true,
                            'message' => "Product mismatched",
                            'data' => (object)['PCS'=>$exist->pcs]
                        ],200
                    );
                }
                

                
            } else {
                return Response::json(
                    [
                        'error' => true,
                        'message' => "Already scanned",
                        'data' => (object) []
                    ],200
                );
            }
        } else {
            return Response::json(
                [
                    'error' => true,
                    'message' => $validator->errors()->first()
                ],400
            );
        }
    }

    public function ps_list(Request $request): JsonResponse
    {
        # not goods out ps list...
        $ids = array();
        $packingslips = PackingslipNew1::where('is_disbursed', 0)->get();
        if(!empty($packingslips)){
            foreach($packingslips as $ps){
                $ids[] = $ps->id;
            }
        }
        $data = Packingslip::select('id','order_id','product_id','slip_no','quantity')->with([
            'order' => function($q){
                $q->select('id','order_no','store_id')->with('stores:id,store_name,bussiness_name');
            }
        ])->with('product:id,name')->whereIn('packingslip_id',$ids)->get();

        foreach($data as $item){
            $scanned_ctn_count = StockBox::where('product_id',$item->product_id)->where('slip_no',$item->slip_no)->where('is_scanned', 1)->count();
            $item->scanned_ctn_count = $scanned_ctn_count;
        }
        // dd($data);

        return response()->json([
            'error' => false,
            'status' => 200,
            'message' => 'Disbusrseable ps product list',
            'data' => array(
                'count_list' => count($data),
                'list' => $data
            )
        ]);
    }

    public function ret_pro_in(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barcode_no' => ['required']
        ]);

        $params = $request->except('_token');

        if (!$validator->fails()) {

            $barcode_no = $params['barcode_no'];

            $checkBarcode = DB::table('return_boxes')->where('barcode_no',$barcode_no)->first();
            if(!empty($checkBarcode)){
                $return_id = $checkBarcode->return_id;
                $checkRet = DB::table('returns')->find($return_id);
                // dd($purchase_order_id);
                if($checkRet->goods_in_type == 'bulk'){
                    return response()->json(
                        [
                            'error' => true,
                            'status' => 200,
                            'message' => 'This barcode is under bulk goods in',
                            'data' => (object)['PCS'=>$checkBarcode->pcs]
                        ]
                    );
                }
                if(empty($checkBarcode->is_scanned)){
                    // $po_weight_val = $checkBarcode->po_weight_val;
                      
                    DB::table('return_boxes')->where('barcode_no',$barcode_no)->update(['is_scanned'=>1,  'updated_at'=>date('Y-m-d H:i:s')]);

                    return response()->json(
                        [
                            'error' => false,
                            'status' => 200,
                            'message' => 'Scanned successfully',
                            'data' => (object)['PCS'=>$checkBarcode->pcs]
                        ]
                    );
                    
                }else{
                    return response()->json(
                        [
                            'error' => true,
                            'status' => 200,
                            'message' => 'Already scanned',
                            'data' => (object)[]
                        ]
                    );
                }
            }else{
                return response()->json(
                    [
                        'error' => true,
                        'status' => 200,
                        'message' => 'No barcode found',
                        'data' => (object)[]
                    ]
                );
            }
            
        } else {
            return response()->json(
                [
                    'status' => 400, 
                    'message' => $validator->errors()->first()
                ]
            );
        }


    }

    /* Return Purchase Order List & Item (Disburseable) */

    public function purchase_return_list(Request $request)
    {
        $return_products = PurchaseReturnProduct::select('id','return_id','product_id','quantity')->with('product:id,name','order:id,order_no')->whereHas('order', function($q){
            $q->where('is_disbursed', 0)->where('is_cancelled', 0);
        })->get();

        return response()->json([
            'error' => false,
            'status' => 200,
            'message' => 'Disbusrseable ps product list',
            'data' => array(
                'count_list' => count($return_products),
                'list' => $return_products
            )
        ]);
    }


    /* Scan Return Purchased Item  */

    public function purchase_return_stockout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'return_id' => 'required|exists:purchase_return_orders,id',
            'product_id' => 'required|exists:products,id',
            'barcode_no' => 'required|exists:stock_boxes,barcode_no'
        ]);

        if(!$validator->fails()){
            $params = $request->except('_token');
            $return_id = $params['return_id'];
            $product_id = $params['product_id'];
            $barcode_no = $params['barcode_no'];

            $return_products = PurchaseReturnProduct::where('product_id',$product_id)->where('return_id', $return_id)->first();
            $quantity = $return_products->quantity;

            $check_product_scanned_count = StockBox::where('purchase_return_id',$return_id)->where('product_id', $product_id)->count();

            if($quantity == $check_product_scanned_count){
                return Response::json(
                    [
                        'error' => true,
                        'message' => $quantity." barcodes already scanned for this item",
                        'data' => (object) []
                    ],200
                );
            }

            $stockbox = StockBox::where('barcode_no', $barcode_no)->first();

            if(!empty($stockbox->is_stock_out)){
                return Response::json(
                    [
                        'error' => true,
                        'message' => "Already stock out this box",
                        'data' => (object) []
                    ],200
                );
            }

            if(!empty($stockbox->is_scanned)){
                return Response::json(
                    [
                        'error' => true,
                        'message' => "Already scanned",
                        'data' => (object) []
                    ],200
                );
            }

            $sum_product_quantity = PurchaseReturnProduct::where('return_id',$return_id)->sum('quantity');

            // dd($sum_product_quantity);

            StockBox::where('barcode_no',$barcode_no)->update([
                'scan_no'=>$barcode_no, 
                'is_scanned'=>1,
                'purchase_return_id' => $return_id,
                'updated_at'=>date('Y-m-d H:i:s')
            ]);

            $stock_product = StockProduct::where('stock_id',$stockbox->stock_id)->where('product_id',$product_id)->first();
            $unit_price = $stock_product->unit_price;
            $piece_price = $stock_product->piece_price;

            if(str_contains($barcode_no, 'RE')){
                
                $piece_price = StockProduct::where('product_id', $product_id)->whereHas('stock', function($stock){
                    $stock->whereHas('purchase_order', function($po){
                        $po->whereNotNull('supplier_id');
                    });
                })->max('piece_price');
                $unit_price = StockProduct::where('product_id', $product_id)->whereHas('stock', function($stock){
                    $stock->whereHas('purchase_order', function($po){
                        $po->whereNotNull('supplier_id');
                    });
                })->max('unit_price');
            }
           
            $purRetBoxArr = array(
                'return_id' => $return_id,
                'product_id' => $product_id,
                'pcs' => $stockbox->pcs,
                'barcode_no' => $stockbox->barcode_no,
                'code_html' => $stockbox->code_html,
                'code_base64_img' => $stockbox->code_base64_img,
                'piece_price' => $piece_price,
                'carton_price' => $unit_price,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            // dd($purRetBoxArr);
            PurchaseReturnBox::insert($purRetBoxArr);

            $currently_stockout_product = StockBox::where('purchase_return_id',$return_id)->where('product_id', $product_id)->count();
            $currently_stockout_product_all = StockBox::where('purchase_return_id',$return_id)->count();

            if($sum_product_quantity == $currently_stockout_product_all){
                $this->ledgerEntryPurchaseReturn($return_id);
            }


            return Response::json([
                'error' => false, 
                'message' => "Scanned successfully", 
                'data' => array(
                    'product_id' => $product_id,
                    'required_product_scan' => $quantity,
                    'count_product_scanned' => $currently_stockout_product,
                    'else_product_scan' => ($quantity - $currently_stockout_product)
                ) 
            ],200);


        } else {
            return Response::json(
                [
                    'error' => true,
                    'message' => $validator->errors()->first()
                ],400
            );
        }


    }

    private function ledgerEntryPurchaseReturn($return_id){


        ### StockLog Entry ###
        $purchase_return_boxes = PurchaseReturnBox::where('return_id',$return_id)->get();
        $total_amount = 0;
        foreach($purchase_return_boxes as $box){
            $stocLogArr = array(
                'product_id' => $box->product_id,
                'entry_date' => date('Y-m-d'),
                'purchase_return_id' => $return_id,
                'quantity' => 1,
                'pcs' => $box->pcs,
                'entry_type' => 'purchase_return',
                'type' => 'out',
                'piece_price' => $box->piece_price,
                'carton_price' => $box->carton_price,
                'total_price' => $box->carton_price,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            StockLog::insert($stocLogArr); 
            $total_amount += $box->carton_price;
        }

        PurchaseReturnOrder::where('id',$return_id)->update([
            'amount' => $total_amount,
            'is_disbursed' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        StockBox::where('purchase_return_id',$return_id)->update([
            'is_stock_out' => 1
        ]);

        ##### Ledger Entry ###
        $return = PurchaseReturnOrder::find($return_id);
        $ledgerArr = array(
            'user_type' => 'supplier',
            'supplier_id' => $return->supplier_id,
            'transaction_id' => $return->order_no,
            'transaction_amount' => $total_amount,
            'is_debit' => 1,
            'bank_cash' => 'bank',
            'entry_date' => date('Y-m-d'),
            'purpose' => 'purchase_return',
            'purpose_description' => 'Purchase Return To Supplier',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        Ledger::insert($ledgerArr);
        ##### Journal Entry ###
        $journalArr = array(
            'transaction_amount' => $total_amount,
            'is_credit' => 1,
            'bank_cash' => 'bank',
            'purpose' => 'purchase_return',
            'purpose_description' => 'Purchase Return To Supplier',
            'purpose_id' => $return->order_no,
            'entry_date' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        Journal::insert($journalArr);
        ######################
    }
}
