@extends('admin.layouts.app')
@section('page', 'Stock Report')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Stock </li>
    </ul> 
    <form action="" id="searchForm">   
    <div class="search__filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                @if (Session::has('message'))
                <div class="alert alert-success" role="alert">
                    {{ Session::get('message') }}
                </div>
                @endif
            </div>
            <div class="col-8">
                <div class="row">                    
                    <div class="col">                        
                        <div class="row  align-items-center">
                            
                                <div class="row justify-content-end">
                                    <div class="col">
                                        
                                    </div>   
                                    <div class="col-auto">
                                        @php
                                            $getStockPriceAll = getStockPriceAll();
                                        @endphp
                                        <span>Total Stock Amount <strong>Rs. {{ number_format((float)$getStockPriceAll, 2, '.', '') }}</strong></span>
                                    </div>                                  
                                    <div class="col-auto">
                                        <a href="{{ route('admin.report.stock-report-csv') }}?search={{$search}}" class="btn btn-outline-success select-md">Download Current Stock CSV</a> 
                                    </div> 
                                    <div class="col-3">
                                        <input type="search" name="search" id="search" class="form-control select-md" placeholder="Search here.." value="{{$search}}" autocomplete="off">                                
                                    </div>
                                </div>
                                {{-- <input type="submit" hidden />  --}}
                                                        
                        </div>                         
                    </div>
                </div>
                
            </div>
        </div>
    </div>    
    </div> 
    <div class="filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">

            </div>
            <div class="col-auto">
                Number of rows: 
            </div>
            <div class="col-auto p-0"> 
                
                <select class="form-control select-md" id="paginate" name="paginate">
                    <option value="25" @if($paginate == 25) selected @endif>25</option>
                    <option value="50" @if($paginate == 50) selected @endif>50</option>
                    <option value="100" @if($paginate == 100) selected @endif>100</option>
                    <option value="200" @if($paginate == 200) selected @endif>200</option>
                </select>
            </div>
            
            <div class="col-auto">                        
                <p>{{$count_products}} Total Records</p>                        
            </div>
        </div>
    </div>
    </form> 
    <div class="row">
        <div class="col-sm-12">    
            <div class="table-responsive">
            <table class="table table-sm table-hover ledger">
                <thead>
                    <tr> 
                        <th>#</th>
                        <th>Product</th>
                        <th>Total No Of Cartons</th>
                        <th>Total No Of Pieces</th>
                        <th>Total Stock Amount</th>
                    </tr>
                </thead>
                <tbody id="ledger_body">
                    @php
                        if(empty(Request::get('page')) || Request::get('page') == 1){
                            $i=1;
                        } else {
                            $i = (((Request::get('page')-1)*$paginate)+1);
                        } 
                    
                    @endphp
                    @forelse ($products as $product)
                    @php
                        $getStockPriceQty = getStockPriceQty($product->id);
                        $sumPiecePrice = $getStockPriceQty['sumPiecePrice'];
                    @endphp
                    <tr class="store_details_row">
                        <td>{{$i}}</td>
                        <td>{{$product->name}}</td>
                        <td>{{ count($product->count_stock) }}</td>
                        <td>{{ ($product->pcs * (count($product->count_stock)) ) }}</td>
                        <td>Rs. {{ number_format((float)$sumPiecePrice, 2, '.', '') }}</td>
                    </tr>
                    
                    @php
                        $i++;
                    @endphp
                    @empty
                        
                    @endforelse                    
                </tbody>
            </table>    
            {{$products->links()}}
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
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
        $("#saveStock").submit(function() {
            
            // $('input').attr('disabled', 'disabled');
            $('#logStock').attr('disabled', 'disabled');
            return true;
        });
    });
    $('#paginate').change(function(){
        $('#searchForm').submit();
    });
    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)        
        $('#searchForm').submit();
    });

    $('#csvLog').submit(function(){
        var entry_date = $('#entry_date').val();
        if(entry_date == ''){
            alert('Please choose date first');
            return false;
        }
    })
    
    
    
    
</script>
@endsection
