@extends('admin.layouts.app')
@section('page', 'CP / SP Report')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>CP / SP</li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="reportForm">
    <div class="row">       
        <div class="col-12">
            <div class="row g-3 align-items-end">                
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">From</label>
                        <input type="date" name="from_date"  id="from_date" class="form-control select-md dates" value="{{ $from_date }}"  max="{{ $to_date }}" placeholder="From" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">To</label>
                        <input type="date" name="to_date"  id="to_date" class="form-control select-md dates" value="{{ $to_date }}" placeholder="To" max="{{ date('Y-m-d') }}" min="{{ $from_date }}" autocomplete="off">  
                    </div>
                </div> 
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="">Search Product</label>
                        <input type="text" name="search" placeholder="Type product name and press enter ..." value="{{$search}}" class="form-control select-md" id="" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-2">
                    <a href="{{ route('admin.report.cp-sp-report') }}?from_date={{$from_date}}&to_date={{$to_date}}" class="btn btn-outline-warning select-md">Clear Search</a>
                </div> 
                <div class="col-auto ms-auto">                    
                    <a href="{{ route('admin.report.cp-sp-csv') }}?from_date={{$from_date}}&to_date={{$to_date}}&search={{$search}}" class="btn btn-success select-md">Export CSV</a>
                </div>                                
            </div>
        </div>                              
    </div>
    </form>   
    <div class="row">
        <div class="col-sm-12">    
            <div class="table-responsive">
            <table class="table table-sm table-hover ledger">
                @forelse ($data as $sub_cat_id => $products) 
                {{-- <strong>{{$sub_cat_id}}</strong> --}}
                <thead>
                    <tr>
                        <th colspan="5">{{getSingleAttributeTable('sub_categories',$sub_cat_id,'name')}}</th>
                        
                    </tr>
                    <tr> 
                        <th>#</th>
                        <th>Product</th>
                        <th>Cost Price</th>
                        <th>Min Sell Price</th>
                        <th>Max Sell Price</th>
                        <th>Pcs per Ctn</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                
                <div>
                    
                </div>
                <tbody id="ledger_body">
                    @php
                        $i=1;
                    @endphp
                    @forelse ($products as $item)
                        @php
                            $getProductMinMaxSellPrice = getProductMinMaxSellPrice($from_date,$to_date,$item->id);
                            $minPrice = $getProductMinMaxSellPrice['minPrice'];
                            $maxPrice = $getProductMinMaxSellPrice['maxPrice'];

                            $checkStockPO = checkStockPO($item->id,0);
                            $stock = $checkStockPO['stock'];
                            $pieces = $checkStockPO['pieces'];
                        @endphp
                        <tr class="store_details_row">
                            <td style="width: 10px;">{{$i}}</td>
                            <td>{{ $item->name }}</td>
                            <td>Rs. {{ number_format((float)$item->cost_price, 2, '.', '') }}</td>
                            <td>Rs. {{ number_format((float)$minPrice, 2, '.', '') }}</td>
                            <td>Rs. {{ number_format((float)$maxPrice, 2, '.', '') }}</td>
                            <td>{{ $item->pcs }}</td>
                            <td>
                                {{$stock}} ctns
                            </td>
                        </tr>

                        @php
                            $i++;
                        @endphp
                        @empty
                            
                        @endforelse                                 
                </tbody>
                @empty
                @endforelse
            </table>    
            </div>
        </div>        
    </div> 
       
</section>
<style>
    /* .table.ledger tr:last-child td {
        background-color: #bee5eb;
    } */
    
</style>
<script>
    
    $("[type='date']").bind('keyup keydown',function (evt) {
        evt.preventDefault();
        alert('Please choose date by clicking on calender icon');
    });

    $('.dates').on('change', function(){
        // var timer;
        // clearTimeout(timer);
        // timer=setTimeout(()=>{            
            $('#reportForm').submit();
        // },3000);
    }); 

    
</script>
@endsection
