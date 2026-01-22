@extends('admin.layouts.app')
@section('page', 'Sales Analysis')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li><a href="{{ route('admin.report.sales-analysis') }}">All Product Sales Analysis</a> </li>
    </ul>
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    @if (!empty($product_ids))
    <div class="col-12" id="single_product_form">
        <div class="row g-3 align-items-end">
            <div class="col-sm-2">
                <div class="form-group">
                    <label for="">From</label>
                    <input type="date" name="from_date" id="from_date" class="form-control  dates"
                        value="{{ $from_date }}" @if(!empty($is_opening_bal)) min="{{ $opening_bal_date }}" @endif
                        max="{{ $to_date }}" min="{{$min_from_date}}" placeholder="From" autocomplete="off">
                </div>
            </div>
            <div class="col-sm-2">
                <div class="form-group">
                    <label for="">To</label>
                    <input type="date" name="to_date" id="to_date" class="form-control  dates" value="{{ $to_date }}"
                        placeholder="To" max="{{ date('Y-m-d') }}" min="{{ $from_date }}" autocomplete="off">
                </div>
            </div>
            <div class="col-auto ">
                <a href="{{route('admin.report.sales-analysis')}}" class="btn btn-outline-warning">Back</a>
            </div>
            <div class="col-auto">
                <a href="{{route('admin.report.sales-analysis-csv', ['from_date'=>$from_date, 'to_date'=>$to_date,'proidc'=>$proidc, 'storeidc'=>$storeidc])}}" class="btn btn-success " onclick="ExportSalesData()" id="export_data">Export CSV</a>
            </div>
            @if (!empty($store_ids) && !empty($product_ids))
                <div class="col-auto">
                    <a class="btn btn-warning " id="resetStoreBtn"
                        href="{{ route('admin.report.sales-analysis') }}?from_date={{$from_date}}&to_date={{$to_date}}&proidc={{$proidc}}">Reset
                        Stores</a>
                </div>
            @endif
        </div>
    </div>
    <div class="row" id="myTable">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table class="table table-sm table-hover ledger">
                    @forelse ($data as $product_id => $products)
                    <thead>
                        <tr>
                            <th colspan="8">{{getSingleAttributeTable('products',$product_id,'name')}}</th>
                        </tr>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Store</th>
                            <th>Order No / Invoice No</th>
                            <th>Total Cartons</th>
                            <th>Total Pieces</th>
                            <th>Rate</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody id="ledger_body">
                        @php
                        $i=1;
                        @endphp
                        @forelse ($products as $item)
                        <tr>
                            <td>{{$i}}</td>
                            <td>{{ date('d/m/Y', strtotime($item->created_at)) }}</td>
                            <td>{{$item->invoice->store->bussiness_name}}</td>
                            <td>

                                {{ $item->invoice->order->order_no }} / {{ $item->invoice->invoice_no }}


                            </td>
                            <td>{{ $item->quantity }} ctns</td>
                            <td>{{ $item->pcs }} pcs</td>
                            <td>Rs. {{ number_format((float)$item->single_product_price, 2, '.', '') }}</td>
                            <td>Rs. {{ number_format((float)$item->total_price, 2, '.', '') }}</td>
                        </tr>
                        @php
                        $i++;
                        @endphp
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center;">No record found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @empty

                    <span>No records found based on this input</span>
                    @endforelse
                </table>
            </div>
        </div>
    </div>
    @else
    <strong>Please choose product first</strong>
    @endif

</section>
<style>
    /* .table.ledger tr:last-child td {
        background-color: #bee5eb;
    } */
</style>
<script>
    var storeIdArr = [];
    var proIdArr = [];
    
    function ExportSalesData(){
        var button = document.getElementById('export_data');
        button.innerHTML = "Please Wait...";
        button.disabled = true;
    }

</script>
@endsection
