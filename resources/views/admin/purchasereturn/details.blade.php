@extends('admin.layouts.app')
@section('page', 'Purchase Return Detail')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li><a href="{{ route('admin.purchasereturn.list') }}">Return</a></li>
            
        <li>Purchase Return Detail</li>
    </ul>    
    <div class="row">
        <div class="col-sm-12" id="invoice-div">
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
                                    <p class="">{{$order->order_no}}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Created Date</label>
                                </div>
                                <div class="col-9">
                                    <p class="">{{ date('j M Y, l', strtotime($order->created_at)) }}</p>
                                </div>
                            </div>
                            @if (!empty($order->amount))
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Amount</label>
                                </div>
                                <div class="col-9">
                                    <p class="">Rs. {{ number_format((float)$order->amount, 2, '.', '') }}</p>
                                </div>
                            </div>
                                
                            @endif
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Supplier</label>
                                </div>
                                <div class="col-9">
                                    <p class="">{{$order->supplier->name}}</p>
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
                                    <th>Item</th>
                                    <th>Barcode</th>
                                    <th>Pcs</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i=1;
                                    $total_amount = 0;
                                @endphp
                                @foreach ($boxes as $item)
                                    @php
                                        $total_amount += $item->carton_price;
                                    @endphp
                                    <tr>
                                        <td>{{$i}}</td>
                                        <td>{{$item->product->name}}</td>  
                                        <td>{{$item->barcode_no}}</td>            
                                        <td>{{$item->pcs}}</td>
                                        <td> Rs. {{ number_format((float)$item->piece_price, 2, '.', '') }}</td>
                                        <td> Rs. {{ number_format((float)$item->carton_price, 2, '.', '') }}</td>
                                    </tr>
                                    @php
                                        $i++;
                                    @endphp
                                @endforeach
                                
                            </tbody>
                            @if (!empty($boxes->toArray()))
                                
                            <tbody>
                                <tr class="table-info">
                                    <td></td>
                                    <td colspan="4">Total Amount</td>
                                    <td>
                                        <span>Rs. {{ number_format((float)$total_amount, 2, '.', '') }}</span>
                                    </td>
                                </tr>
                            </tbody>
                            @else
                            <tbody>
                                <tr class="">
                                    <td colspan="6" style="text-align: center;">No records found !!! </td>
                                    
                                </tr>
                            </tbody>
                            @endif
                        </table>
                    </div>
                    
                </div>
            </div>                
        </div>
        
    </div>    
</section>
@endsection

@section('script')

@endsection