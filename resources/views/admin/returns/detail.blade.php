@extends('admin.layouts.app')
@section('page', 'Return Details')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li><a href="{{ route('admin.returns.list') }}">Returns</a></li>
        <li>{{$data->order_no}}</li>
    </ul>    
    <div class="row">
        <div class="col-sm-9" id="invoice-div">
            <div class="card shadow-sm">
                <div class="card-body">                    
                    <div class="admin__content">
                        <aside>
                            <nav>Order Information</nav>
                        </aside>
                        {{-- <content>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Order Id</label>
                                </div>
                                <div class="col-9">
                                    <p class="">#{{$stock->purchase_order->unique_id}}</p>
                                </div>
                            </div>
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
                        </content> --}}
                    </div>                    
                </div>
            </div>                            
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-sm" id="timePriceTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Pcs per Ctns</th>
                                    <th>No of Cartons</th>
                                    <th>Total Pieces</th>
                                    <th>Price per Pcs</th>
                                    <th>Price per Ctn</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i=1;
                                    $totalPcs = 0;
                                    $totalCtns = 0;
                                @endphp
                                @foreach ($data->return_products as $item)
                                @php
                                    
                                    $totalCtns += $item->quantity;
                                    $totalPcs += ($item->pcs * $item->quantity);
                                @endphp
                                    <tr>
                                        <td>{{$i}}</td>
                                        <td>{{$item->product->name}}</td>                        
                                        <td> {{$item->pcs}} pcs</td>              
                                        <td> {{$item->quantity}} ctns</td>
                                        <td> {{ ($item->pcs * $item->quantity) }} pcs </td>
                                        <td> Rs. {{ number_format((float)$item->piece_price, 2, '.', '') }}</td>
                                        <td> Rs. {{ number_format((float)$item->unit_price, 2, '.', '') }}</td>
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
                                    <td>Total Return</td>
                                    <td></td>
                                    <td>{{$totalCtns}} ctns</td>
                                    <td>{{$totalPcs}} pcs</td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <span>Rs. {{ number_format((float)$data->amount, 2, '.', '') }}</span>
                                    </td>
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
                    
                    <a href="{{ route('admin.returns.list') }}" class="btn btn-sm btn-danger select-md">Back to Return List </a>
                   
                    
                    {{-- <a href="{{ route('admin.grn.barcodes',$id) }}" class="btn btn-sm btn-outline-info select-md">Download Barcodes</a> --}}
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