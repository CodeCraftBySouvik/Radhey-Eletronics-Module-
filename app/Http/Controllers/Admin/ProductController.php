<?php

namespace App\Http\Controllers\Admin;

use App\Interfaces\ProductInterface;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\StockProduct;
use App\Models\InvoiceProduct;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // private ProductInterface $productRepository;

    public function __construct(ProductInterface $productRepository)
    {
        $this->productRepository = $productRepository;
        
        $this->middleware('auth:web');
        $this->middleware(function ($request, $next) {
            $this->id = Auth::user()->id;
            $this->type = Auth::user()->type;
            $this->designation = Auth::user()->designation;
            // dd($this->type);
            if($this->type == 2){
                $userAccesses = userAccesses($this->designation,4);
                if(!$userAccesses){
                    abort(401);
                }
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $term = !empty($request->term)?$request->term:'';
        $paginate = !empty($request->paginate)?$request->paginate:25;

        $data = Product::select('*');
        $total = Product::select('id');
        
        if(!empty($term)){
            $data = $data->where('name', 'LIKE', '%' . $term . '%');
            $total = $total->where('name', 'LIKE', '%' . $term . '%');
        }        

        $data = $data->orderBy('id','desc')->paginate($paginate);
        $total = $total->count();

        

        $data = $data->appends(['term'=>$term,'page'=>$request->page,'paginate'=>$paginate]);
        
        return view('admin.product.index', compact('data','term','total','paginate'));
    }

    

    public function subcategoriesByCategory(Request $request)
    {
        # through ajax call...
        $cat_id = !empty($request->cat_id)?$request->cat_id:'';

        $data = $this->productRepository->subCategoryList(1,$cat_id);
        return $data;
    }

    public function create(Request $request)
    {
        $categories = $this->productRepository->categoryList(1);    
        $supplier = DB::table('suppliers')->select('id','name')->where('status',1)->get();
       
        return view('admin.product.create', compact('categories','supplier'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            "cat_id" => "required|exists:categories,id",
            "sub_cat_id" => "required|exists:sub_categories,id",
            "name" => "required|string|max:50|unique:products,name",
            // "hsn_code" => "required|max:50",
            // "pcs" => "required",
            // "short_desc" => "required",
            // "desc" => "required",
            // "igst" => "required",
            // "cgst" => "required",
            // "sgst" => "required",            
            // "image" => "required",
            // "product_images" => "nullable|array"
        ],[
            "cat_id.required" => "Please choose category",
            "cat_id.exists" => "Unknown category",
            "sub_cat_id.required" => "Please choose sub category",
            "sub_cat_id.exists" => "Unknown sub category",
            "name.required" => "Please mention product name",
            "name.max" => "Please maintain product name within 150 character",
            "name.unique" => "Already exists product name",
            // "short_desc.required" => "Please add short description of product",
            // "desc.required" => "Please add some description of product",
            // "image.required" => "Please add an image of product"
        ]);

        $params = $request->except('_token');
        // dd($params);
        $storeData = $this->productRepository->create($params);
        // dd($storeData);
        if ($storeData) {

            changelogentry(Auth::user()->id,'add_product',json_encode($params));


            Session::flash('message', 'Product created successfully');
            return redirect()->route('admin.product.index');
        } else {
            return redirect()->route('admin.product.create')->withInput($request->all());
        }
    }

    public function show(Request $request, $id)
    {
        $data = $this->productRepository->listById($id);
        $images = $this->productRepository->listImagesById($id);
        // dd($images);
        return view('admin.product.detail', compact('data', 'images'));
    }

    public function viewDetail(Request $request)
    {
        $id = $request->id;
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $data =  Product::where('id',$id)->first();
        $data->warning = null;
        $data->left_pcs = null;
        $count_stock = DB::table('stock_boxes')->where('product_id',$id)->where('is_scanned', 0)->where('is_stock_out', 0)->count();
        if($count_stock==1){
            $stock_pcs = $data->pcs;
            $fetch_stock_pcs = DB::table('stock_boxes')->where('product_id', $id)->where('is_scanned', 0)->where('is_stock_out', 0)->value('pcs');
            if($data->pcs>$fetch_stock_pcs){
                $data->pcs = $fetch_stock_pcs;
                $data->threshold_price = $data->cost_price*$fetch_stock_pcs;
                $data->warning ="<p>This is last stock <strong>Pcs per Ctn (".$stock_pcs.")</strong> & <strong>Available Pcs(".$fetch_stock_pcs.")</strong></p>";
                $data->total_pcs = $stock_pcs;
            }
        }
        $data->count_stock = $count_stock;
        $piece_price = 0;
        $last_three_order_product = array();
        if(!empty($store_id)){
            
            $order_ids = Order::where('store_id',$store_id)->orderBy('id','desc')->pluck('id')->toArray();
            $order_ids = array_values($order_ids);
            
            // dd($order_ids);
            if(!empty($order_ids)){
                $last_three_order_product = DB::table('order_products')->select('piece_price','qty','pcs','created_at')->selectRaw("DATE_FORMAT(created_at,'%d/%m/%Y') AS created_date")->where('product_id',$id)->whereIn('order_id',$order_ids)->take(3)->orderBy('id','desc')->get();
                // dd($last_three_order_product);

                $store_last_order = Order::where('store_id',$store_id)->orderBy('id','desc')->first();
                if(!empty($store_last_order)){
                    $last_order_product = DB::table('order_products')->select('piece_price','qty','pcs','created_at')->selectRaw("DATE_FORMAT(created_at,'%d/%m/%Y') AS created_date")->where('product_id',$id)->whereIn('order_id',$order_ids)->orderBy('id','desc')->first();
                    if(!empty($last_order_product)){
                        $piece_price = $last_order_product->piece_price;
                    }
                }

                
            }            
        }
        $data->piece_price = $piece_price;
        $data->last_three_order_product = $last_three_order_product;
        
        return $data;
    }
    public function viewDetailPcsPerCtn(Request $request)
    {
        $id = $request->id;
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $data =  Product::where('id',$id)->first();
        $data->warning = null;
        $data->left_pcs = null;
        $count_stock = DB::table('stock_boxes')->where('product_id',$id)->where('is_scanned', 0)->where('is_stock_out', 0)->count();
        if($count_stock==1){
            $stock_pcs = $data->pcs;
            $fetch_stock_pcs = DB::table('stock_boxes')->where('product_id', $id)->where('is_scanned', 0)->where('is_stock_out', 0)->value('pcs');
            if($data->pcs>$fetch_stock_pcs){
                $data->pcs = $fetch_stock_pcs;
                $data->threshold_price = $data->cost_price*$fetch_stock_pcs;
                $data->warning ="<p>This is last stock <strong>Pcs per Ctn (".$stock_pcs.")</strong> & <strong>Available Pcs(".$fetch_stock_pcs.")</strong></p>";
                $data->total_pcs = $stock_pcs;
            }
        }
        $data->count_stock = $count_stock;
        $piece_price = 0;
        $last_three_order_product = array();
        if(!empty($store_id)){
            
            $order_ids = Order::where('store_id',$store_id)->orderBy('id','desc')->pluck('id')->toArray();
            $order_ids = array_values($order_ids);
            
            // dd($order_ids);
            if(!empty($order_ids)){
                $last_three_order_product = DB::table('order_products')->select('piece_price','qty','pcs','created_at')->selectRaw("DATE_FORMAT(created_at,'%d/%m/%Y') AS created_date")->where('product_id',$id)->whereIn('order_id',$order_ids)->take(3)->orderBy('id','desc')->get();
                // dd($last_three_order_product);

                $store_last_order = Order::where('store_id',$store_id)->orderBy('id','desc')->first();
                if(!empty($store_last_order)){
                    $last_order_product = DB::table('order_products')->select('piece_price','qty','pcs','created_at')->selectRaw("DATE_FORMAT(created_at,'%d/%m/%Y') AS created_date")->where('product_id',$id)->whereIn('order_id',$order_ids)->orderBy('id','desc')->first();
                    if(!empty($last_order_product)){
                        $piece_price = $last_order_product->piece_price;
                    }
                }

                
            }            
        }
        $data->piece_price = $piece_price;
        $data->last_three_order_product = $last_three_order_product;
        
        return $data;
    }
     public function viewAllDetail(Request $request)
    {
        $store_id = !empty($request->store_id) ? $request->store_id : '';
        
        // Get all products from the Product table
        $products = Product::all();
    
        // Loop through each product and apply the logic
        foreach ($products as $product) {
            $id = $product->id;
    
            // Count stock for the current product
            $count_stock = DB::table('stock_boxes')
                ->where('product_id', $id)
                ->where('is_scanned', 0)
                ->where('is_stock_out', 0)
                ->count();
            $product->count_stock = $count_stock;
    
            $piece_price = 0;
            $last_three_order_product = array();
    
            if (!empty($store_id)) {
                $order_ids = Order::where('store_id', $store_id)
                    ->orderBy('id', 'desc')
                    ->pluck('id')
                    ->toArray();
                $order_ids = array_values($order_ids);
    
                if (!empty($order_ids)) {
                    $last_three_order_product = DB::table('order_products')
                        ->select('piece_price', 'qty', 'pcs', 'created_at')
                        ->selectRaw("DATE_FORMAT(created_at, '%d/%m/%Y') AS created_date")
                        ->where('product_id', $id)
                        ->whereIn('order_id', $order_ids)
                        ->take(3)
                        ->orderBy('id', 'desc')
                        ->get();
    
                    $store_last_order = Order::where('store_id', $store_id)
                        ->orderBy('id', 'desc')
                        ->first();
    
                    if (!empty($store_last_order)) {
                        $last_order_product = DB::table('order_products')
                            ->select('piece_price', 'qty', 'pcs', 'created_at')
                            ->selectRaw("DATE_FORMAT(created_at, '%d/%m/%Y') AS created_date")
                            ->where('product_id', $id)
                            ->whereIn('order_id', $order_ids)
                            ->orderBy('id', 'desc')
                            ->first();
                        if (!empty($last_order_product)) {
                            $piece_price = $last_order_product->piece_price;
                        }
                    }
                }
            }
    
            // Assign values to the product object
            $product->piece_price = $piece_price;
            $product->last_three_order_product = $last_three_order_product;
        }
    
        // Return all products with their additional data
        return $products;
    }

    public function searchByName(Request $request)
    {
        # ajax search by name...
        $term = !empty($request->term)?$request->term:'';
        $supplier_id = !empty($request->supplier_id)?$request->supplier_id:'';
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $idnotin = !empty($request->idnotin)?$request->idnotin:array();

        $data = DB::table('products')->select('*')->where('name', 'LIKE', '%'.$term.'%')->where('status', 1);
        
        if(!empty($idnotin)){
            $data = $data->whereNotIn('id', $idnotin);
        }

        ### For Supplier Purchase Return ###
        if(!empty($supplier_id)){
            $stockProdIds = StockProduct::whereHas('stock', function($sp) use ($supplier_id){
                $sp->whereHas('purchase_order', function($po) use ($supplier_id){
                    $po->where('supplier_id', $supplier_id);
                });
            })->distinct()->pluck('product_id')->toArray();
            // if(!empty($stockProdIds)){
                $data = $data->whereIn('id', $stockProdIds);
            // }            
        }
        ### For Supplier Purchase Return ###
        if(!empty($store_id)){
            $invoiceProdIds = InvoiceProduct::whereHas('invoice', function ($inv) use ($store_id){
                $inv->where('store_id', $store_id);
            })->distinct()->pluck('product_id')->toArray();
            // dd($invoiceProdIds);
            // if(!empty($invoiceProdIds)){
                $data = $data->whereIn('id', $invoiceProdIds);
            // } else {
            //     $data = array();
            // }
        }

        
        $data = $data->orderBy('name','asc')->get();
        return $data;
    }
    public function searchByNameForStock(Request $request)
    {
        # ajax search by name for stock...
        $term = !empty($request->term)?$request->term:'';
        $supplier_id = !empty($request->supplier_id)?$request->supplier_id:'';
        $store_id = !empty($request->store_id)?$request->store_id:'';
        $idnotin = !empty($request->idnotin)?$request->idnotin:array();

        // $data = DB::table('products')->select('*')->where('name', 'LIKE', '%'.$term.'%')->where('status', 1);
       $data = DB::table('products')
        ->select(
            'products.name',
            'products.id',
            'stock_boxes.pcs',
            DB::raw('COUNT(stock_boxes.pcs) as ctn')
        )
        ->join('stock_boxes', 'stock_boxes.product_id', '=', 'products.id')
        ->where('products.name', 'LIKE', '%' . $term . '%')
        ->where('products.status', 1)
        ->where('stock_boxes.is_stock_out', 0)
        ->groupBy('products.id', 'products.name', 'stock_boxes.pcs') // Group by product ID and other selected columns
        ->orderByDesc('stock_boxes.pcs') // Order by pcs in descending order
        ->get();
        $data2 = DB::table('products')
            ->select(
                'products.name',
                'products.id',
                'stock_boxes.pcs',
                 DB::raw('SUM(CASE WHEN stock_boxes.is_stock_out = 1 THEN 0 ELSE 1 END) as ctn') 
            )
            ->join('stock_boxes', 'stock_boxes.product_id', '=', 'products.id')
            ->where('products.name', 'LIKE', '%' . $term . '%')
            ->where('products.status', 1)
            ->groupBy('products.id', 'products.name', 'stock_boxes.pcs') // Group by product ID and other selected columns
            ->havingRaw('SUM(CASE WHEN stock_boxes.is_stock_out = 0 THEN 1 ELSE 0 END) = 0') // Ensure no stock_box has is_stock_out = 0
            ->orderByDesc('stock_boxes.pcs') // Order by pcs in descending order
            ->get();
            $data = $data->merge($data2);
        // if(!empty($idnotin)){
        //     $data = $data->whereNotIn('id', $idnotin);
        // }

        // ### For Supplier Purchase Return ###
        // if(!empty($supplier_id)){
        //     $stockProdIds = StockProduct::whereHas('stock', function($sp) use ($supplier_id){
        //         $sp->whereHas('purchase_order', function($po) use ($supplier_id){
        //             $po->where('supplier_id', $supplier_id);
        //         });
        //     })->distinct()->pluck('product_id')->toArray();
        //     // if(!empty($stockProdIds)){
        //         $data = $data->whereIn('id', $stockProdIds);
        //     // }            
        // }
        // ### For Supplier Purchase Return ###
        // if(!empty($store_id)){
        //     $invoiceProdIds = InvoiceProduct::whereHas('invoice', function ($inv) use ($store_id){
        //         $inv->where('store_id', $store_id);
        //     })->distinct()->pluck('product_id')->toArray();
        //     // dd($invoiceProdIds);
        //     // if(!empty($invoiceProdIds)){
        //         $data = $data->whereIn('id', $invoiceProdIds);
        //     // } else {
        //     //     $data = array();
        //     // }
        // }

        
        // $data = $data->orderBy('name','asc')->get();
        return $data;
    }

    

    public function edit(Request $request, $id)
    {
        $categories = $this->productRepository->categoryList(1);
        $sub_categories = $this->productRepository->subCategoryList(1);
        $data = $this->productRepository->listById($id);
        $images = $this->productRepository->listImagesById($id);
        $supplier = DB::table('suppliers')->select('id','name')->where('status',1)->get();
        
        // dd($data);

        return view('admin.product.edit', compact('id', 'data', 'categories', 'sub_categories', 'images','supplier'));
    }

    public function update(Request $request)
    {
        // dd($request->all());

        $request->validate([
            "cat_id" => "required|exists:categories,id",
            "sub_cat_id" => "required|exists:sub_categories,id",
            "name" => "required|string|max:50|unique:products,name,".$request->product_id,
            // "hsn_code" => "required|max:50",
            // "pcs" => "required",
            // "short_desc" => "required",
            // "desc" => "required",
            // "igst" => "required",
            // "cgst" => "required",
            // "sgst" => "required",            
            // "image" => "required_if_null:image",
            // "product_images" => "nullable|array"
        ],[
            "cat_id.required" => "Please choose category",
            "cat_id.exists" => "Unknown category",
            "sub_cat_id.required" => "Please choose sub category",
            "sub_cat_id.exists" => "Unknown sub category",
            "name.required" => "Please mention product name",
            "name.max" => "Please maintain product name within 150 character",
            "name.unique" => "Already exists product name",
            // "short_desc.required" => "Please add short description of product",
            // "desc.required" => "Please add some description of product",
            // "image.required" => "Please add an image of product"
        ]);
        // dd($request->product_id);
        $params = $request->except('_token');
        // dd($params);
        $storeData = $this->productRepository->update($request->product_id, $params);

        if ($storeData) {

            changelogentry(Auth::user()->id,'edit_product',json_encode($params));

            Session::flash('message', 'Product updated successfully');
            return redirect()->route('admin.product.index');
        } else {
            // dd($request->all());
            return redirect()->route('admin.product.update', $request->product_id)->withInput($request->all());
        }
    }

    public function status(Request $request, $id)
    {
        $storeData = $this->productRepository->toggle($id);

        if ($storeData) {
            Session::flash('message', 'Status changed successfully');
            return redirect()->route('admin.product.index');
        } else {
            return redirect()->route('admin.product.create')->withInput($request->all());
        }
    }

    public function destroy(Request $request, $id)
    {
        $this->productRepository->delete($id);

        return redirect()->route('admin.product.index');
    }

    public function destroySingleImage(Request $request, $id)
    {
        $this->productRepository->deleteSingleImage($id);
        return redirect()->back();

        // return redirect()->route('admin.product.index');
    }  

    public function bulkSuspend(Request $request)
    {
        if(!empty($request->suspend_check)){
            $count_data = count($request->suspend_check);
            $data = $this->productRepository->bulkSuspend($request->suspend_check);

            Session::flash('message', $count_data.' products suspended successfully');
            return redirect()->route('admin.product.index');
        }else{
            return redirect()->route('admin.product.index');
        }
    }

    
    
}