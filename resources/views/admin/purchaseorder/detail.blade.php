@extends('admin.layouts.app')
@section('page', 'Purchase Order Detail')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li><a href="{{ route('admin.purchaseorder.index') }}?type=po">PO</a></li>
            
        <li>Purchase Order Detail</li>
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
                                    <p class="">#{{$po->unique_id}}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Supplier</label>
                                </div>
                                <div class="col-9">
                                    <p class="">{{$po->supplier->name}}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Contact</label>
                                </div>
                                <div class="col-9">
                                    <p class="">{{$po->supplier->mobile}}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Email</label>
                                </div>
                                <div class="col-9">
                                    <p class="">{{$po->supplier->email}}</p>
                                </div>
                            </div>
                        </content>
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
                                    <th>No of Cartons</th>
                                    <th>Pieces Per Carton</th>
                                    <th>Total No Of Pieces</th>
                                    {{-- <th>Weight per Ctn</th> --}}
                                    @if (Auth::user()->designation == null)
                                    <th>Cost Price Per Piece</th>
                                    @endif
                                    
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i=1;
                                    $totalCtns = $totalPcs = $totalCostPrice = 0;
                                @endphp
                                @foreach ($data as $item)
                                @php
                                    $total_pcs = ($item->pcs*$item->qty);
                                    $totalPcs += $total_pcs;
                                    $totalCtns += $item->qty;
                                    $totalCostPrice += $item->piece_price;
                                @endphp
                                    <tr>
                                        <td>{{$i}}</td>
                                        <td>{{$item->product}}</td>                                        
                                        <td>{{$item->qty}} ctns</td>
                                        <td>{{$item->pcs}} pcs</td> 
                                        <td>{{$total_pcs}} pcs</td>
                                        {{-- <td> {{$item->weight}} {{$item->weight_unit}}</td>                                        --}}
                                        @if (Auth::user()->designation == null)
                                        <td> Rs. {{ number_format((float)$item->piece_price, 2, '.', '') }}</td>
                                        @endif
                                        <td> Rs. {{ number_format((float)$item->total_price, 2, '.', '') }}</td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                                
                            </tbody>
                            <tbody>
                                <tr class="table-info">
                                    @if (Auth::user()->designation == null)
                                        
                                    <td></td>
                                    @endif
                                    <td>Total PO Price</td>
                                    <td>{{$totalCtns}} ctns</td>
                                    <td></td>
                                    <td>{{$totalPcs}} pcs</td>
                                    <td>Rs. {{ number_format((float)$totalCostPrice, 2, '.', '') }}</td>
                                    <td><span>Rs. {{ number_format((float)$po->total_price, 2, '.', '') }}</span></td>
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
                    <a href="{{ route('admin.purchaseorder.index') }}" class="btn btn-sm btn-danger select-md">Back to PO </a>
                    {{-- <a onclick='printtag({{$data}})' class="btn btn-sm btn-outline-info">Print</a> --}}
                    <a href="{{ route('admin.barcodes', $id) }}" class="btn btn-sm btn-outline-info select-md">Download Barcodes</a>
                </div>
            </div>
        </div>
    </div>    
</section>
@endsection

@section('script')


@endsection