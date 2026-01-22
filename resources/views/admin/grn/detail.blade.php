@extends('admin.layouts.app')
@section('page', 'GRN Detail')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li><a href="{{ route('admin.grn.list') }}">GRN</a></li>
        <li>{{$stock->grn_no}}</li>
    </ul>    
    <div class="row">
        <div class="col-sm-9" id="invoice-div">
            <div class="card shadow-sm">
                <div class="card-body">                    
                    <div class="admin__content">
                        <aside>
                            <nav>Order Information</nav>
                        </aside>
                        <content>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Order Id</label>
                                </div>
                                <div class="col-9">
                                    @if (!empty($stock->purchase_order))
                                        <p class="">#{{$stock->purchase_order->unique_id}}</p>
                                    @else
                                        <p class="">#{{$stock->returns->order_no}}</p>
                                    @endif
                                    
                                </div>
                            </div>
                            @if (!empty($stock->purchase_order))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Supplier</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock->purchase_order->supplier->name}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Contact</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock->purchase_order->supplier->mobile}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Email</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock->purchase_order->supplier->email}}</p>
                                    </div>
                                </div>
                            @else
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Store</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock->returns->store->bussiness_name}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Contact</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="">{{$stock->returns->store->contact}}</p>
                                    </div>
                                </div>
                            @endif
                            
                        </content>
                    </div>                    
                </div>
            </div>                            
            <div class="row">
                <div class="col-md-12">
                    <div class="tbale-responsive">
                        <table class="table table-sm" id="timePriceTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>No of Cartons</th>
                                    <th>Pieces Per Carton</th>
                                    <th>Total No Of Pieces</th>
                                    <th>Cost Price Per Piece</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i=1;
                                    $totalCtns = $totalPcs = $totalCostPrice = 0;
                                @endphp
                                @foreach ($stock_products as $item)
                                @php
                                    $totalCtns += $item->quantity;
                                    $totalCostPrice += $item->piece_price;
                                    $purchase_order_id = $item->stock->purchase_order_id;
                                    $return_id = $item->stock->return_id;


                                    if(!empty($purchase_order_id)){
                                        $po_prod = getPODetails($purchase_order_id,$item->product_id);
                                        $prod_pcs = $po_prod->pcs;
                                        $total_pcs = ($prod_pcs * $item->quantity);
                                        $totalPcs += $total_pcs;
                                    }
                                    

                                    if(!empty($return_id)){
                                        $return_prod = getReturnDetails($return_id,$item->product_id);
                                        $prod_pcs = $return_prod->pcs;
                                        $total_pcs = ($prod_pcs * $item->quantity);
                                        $totalPcs += $total_pcs;
                                    }
                                    


                                @endphp
                                    <tr>
                                        <td>{{$i}}</td>
                                        <td>{{$item->product->name}}</td>                                      
                                        <td>{{ $item->quantity }} ctns</td>
                                        <td>{{ $prod_pcs }} pcs</td>
                                        <td>{{ $total_pcs }} pcs</td>
                                        <td> Rs. {{ number_format((float)$item->piece_price, 2, '.', '') }}</td>
                                        <td> Rs. {{ number_format((float)$item->total_price, 2, '.', '') }}</td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                                
                            </tbody>
                            <tbody>
                                <tr class="table-info">
                                    <td></td>
                                    <td>Total GRN Value</td>
                                    <td>{{ $totalCtns }} ctns</td>
                                    <td></td>
                                    <td>{{ $totalPcs }} pcs</td>
                                    <td><span>Rs. {{ number_format((float)$totalCostPrice, 2, '.', '') }}</span></td>
                                    <td><span>Rs. {{ number_format((float)$stock->total_price, 2, '.', '') }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>                
        </div>
        <div class="col-sm-3">
            <div class="card shadow-sm">
                <div class="card-header" id="btnDownload">
                    Action
                </div>
                <div class="card-body text-end">
                    
                    <a href="{{ route('admin.grn.list') }}" class="btn btn-sm btn-danger select-md">Back to GRN </a>
                   
                    
                    <a href="{{ route('admin.grn.barcodes',$id) }}" class="btn btn-sm btn-outline-info select-md">Download Barcodes</a>
                </div>
            </div>
        </div>
    </div>    
</section>
@endsection

@section('script')
<script>
   
</script>
@endsection