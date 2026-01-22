@extends('admin.layouts.app')
@section('page', 'Barcode Info')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li>Barcode Info</li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="">
    <div class="row">       
        <div class="col-12">
            <div class="row g-3 align-items-end">                
                
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">Barcode</label>
                        <input type="text" name="barcode_no" value="{{$barcode_no}}" class="form-control" id="" maxlength="16" placeholder="Enter barcode no ... " autocomplete="off">
                    </div>
                </div> 
               
                <div class="col-auto me-auto">
                    @if (!empty($barcode_no))
                        <a class="btn btn-warning" href="{{ route('admin.report.barcode-history') }}">Clear</a>                        
                    @endif
                    <button class="btn btn-success ">Search</button>
                </div>                
            </div>
        </div>  
        
        
                     
    </div>
    </form>  
    
    @if (empty($barcode_no))
        
    @else
        @if ($isItemFound)           
        <div class="row" id="product-div">
            <div class="col-sm-9" >
                <div class="card shadow-sm">
                    <div class="card-body">                    
                        <div class="admin__content">
                            <aside>
                                <nav>Item Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Product</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$product->name}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Category</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$product->category->name}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Subcategory</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$product->subCategory->name}}</p>
                                    </div>
                                </div>
                            </content>
                        </div>                    
                    </div>
                </div>                            
                            
            </div>
        </div> 
        @else
        <div class="row" id="product-div">
            <div class="col-sm-9" >
                <div class="card shadow-sm">
                    <div class="card-body">                    
                        <div class="admin__content">
                            <aside>
                                <nav>Item Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">No product found !!! </label>
                                    </div>
                                </div>
                                
                            </content>
                        </div>                    
                    </div>
                </div>                            
                            
            </div>
        </div> 
        @endif
        @if ($isPurchaseOrder)
        <div class="row" id="po-div">
            <div class="col-sm-9" >
                <div class="card shadow-sm">
                    <div class="card-body">                    
                        <div class="admin__content">
                            <aside>
                                <nav>Purchase Order Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Order Id</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$purchase_order_box->purchase_orders->unique_id}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Supplier</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$purchase_order_box->purchase_orders->supplier->name}}</p>
                                    </div>
                                </div>
                                @if (empty(Auth::user()->designation))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Rate</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">Rs. {{ number_format((float)$purchase_order_box->piece_price, 2, '.', '') }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Pieces per Carton</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$purchase_order_box->pcs}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Date</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{date('j M Y, l',strtotime($purchase_order_box->purchase_orders->created_at))}}</p>
                                    </div>
                                </div>
                            </content>
                        </div>                    
                    </div>
                </div>                            
                            
            </div>
        </div>
        @endif
        @if ($isStoreReturn)            
        <div class="row" id="storereturn-div">
            <div class="col-sm-9" >
                <div class="card shadow-sm">
                    <div class="card-body">                    
                        <div class="admin__content">
                            <aside>
                                <nav>Store Return Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Order Id</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$return_boxes->returns->order_no}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Store</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$return_boxes->returns->store->bussiness_name}}</p>
                                    </div>
                                </div>
                                @if (empty(Auth::user()->designation))
                                    
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Rate</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">Rs. {{ number_format((float)$return_product->piece_price, 2, '.', '') }}</p>
                                    </div>
                                </div>                                
                                @endif
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Pieces per Carton</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{ $return_boxes->pcs }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Date</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{date('j M Y, l',strtotime($return_boxes->returns->created_at))}}</p>
                                    </div>
                                </div>
                            </content>
                        </div>                    
                    </div>
                </div>                            
                            
            </div>
        </div>
        @endif
        @if ($isGrn)          
        <div class="row" id="grn-div">
            <div class="col-sm-9" >
                <div class="card shadow-sm">
                    <div class="card-body">                    
                        <div class="admin__content">
                            <aside>
                                <nav>GRN Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">GRN ID</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock_boxes->stock->grn_no}}</p>
                                    </div>
                                </div>
                                @if (!empty($stock_boxes->stock->purchase_order))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Supplier</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock_boxes->stock->purchase_order->supplier->name}}</p>
                                    </div>
                                </div>
                                @else
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Store</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock_boxes->stock->returns->store->bussiness_name}}</p>
                                    </div>
                                </div>
                                @endif  
                                @if (empty(Auth::user()->designation))                              
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Rate</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">Rs. {{ number_format((float)$stock_boxes->piece_price, 2, '.', '') }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Pieces per Carton</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{ $stock_boxes->pcs }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Date</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{date('j M Y, l',strtotime($stock_boxes->stock->created_at))}}</p>
                                    </div>
                                </div>
                            </content>
                        </div>                    
                    </div>
                </div>                            
                            
            </div>
        </div>  
        @endif
        @if ($isSalesOut)            
        <div class="row" id="sales-div">
            <div class="col-sm-9" >
                <div class="card shadow-sm">
                    <div class="card-body">                    
                        <div class="admin__content">
                            <aside>
                                <nav>Sales Order Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Order Id</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$packingslip->order->order_no}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Store</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$packingslip->order->stores->bussiness_name}}</p>
                                    </div>
                                </div>
                                @if (empty(Auth::user()->designation))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Rate</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">Rs. {{ number_format((float)$packingslip->order_product->piece_price, 2, '.', '') }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Pieces per Carton</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{ $stock_boxes->pcs }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Date</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{date('j M Y, l',strtotime($packingslip->order_product->orders->created_at))}}</p>
                                    </div>
                                </div>
                            </content>
                        </div>                    
                    </div>
                </div>                            
                            
            </div>
        </div>
        @endif
        
        @if ($isPurchaseReturn)            
        <div class="row" id="purchasereturn-div">
            <div class="col-sm-9" >
                <div class="card shadow-sm">
                    <div class="card-body">                    
                        <div class="admin__content">
                            <aside>
                                <nav>Purchase Return Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Order Id</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock_boxes->purchase_return->order_no}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Supplier</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock_boxes->purchase_return->supplier->name}}</p>
                                    </div>
                                </div>
                                @if (empty(Auth::user()->designation))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Rate</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">Rs. {{ number_format((float)$purchase_return_box->piece_price, 2, '.', '') }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Pieces per Carton</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$purchase_return_box->pcs}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Date</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{date('j M Y, l',strtotime($stock_boxes->purchase_return_date))}}</p>
                                    </div>
                                </div>
                            </content>
                        </div>                    
                    </div>
                </div>                            
                            
            </div>
        </div>
        @endif

      
    @endif

    
       
</section>
<style>
    
    
</style>
<script>
    
</script>
@endsection
