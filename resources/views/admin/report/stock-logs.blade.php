@extends('admin.layouts.app')
@section('page', 'Daily Stock Logs')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li><a href="{{ route('admin.report.stock-ledger') }}">Daily Stock Logs</a> </li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="ledgerForm">
    <div class="row">       
        <div class="col-12">
            <div class="row g-3 align-items-end">
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">Date</label>
                        <input type="date" name="entry_date"  id="entry_date" class="form-control select-md dates" value="{{ $entry_date }}"  max="{{ date('Y-m-d') }}"   autocomplete="off">
                    </div>
                </div>
                
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="" id="lable_user">
                            Product                  
                        </label> 
                        <input type="text" name="product_name" placeholder="Please Search Product First" class="form-control select-md" value="{{$product_name}}" id="searchProText" onkeyup="getProductByName(this.value);" autocomplete="off">      
                        <input type="hidden" name="product_id" id="searchProId" value="{{$product_id}}">
                                        
                    </div>
                    <div class="respDrop" id="respDrop"></div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="" id="lable_user">
                            Purpose                  
                        </label> 
                        <select name="type" class="form-control select-md" id="type">
                            <option value="">All</option>
                            <option value="in" @if($type == 'in') selected @endif>GOODS RECEIVED</option>
                            <option value="out" @if($type == 'out') selected @endif>GOODS DISBURSED</option>
                        </select>
                                        
                    </div>
                    <div class="respDrop" id="respDrop"></div>
                </div>
                <div class="col-auto ms-auto">
                    @if (!empty($product_id))
                        <a href="{{ route('admin.report.stock-log') }}?entry_date={{$entry_date}}" class="btn btn-warning select-md">Reset Filter</a>
                    @endif
                    @if (!empty($data->toArray()))
                        <a href="{{ route('admin.report.stock-log-csv') }}?entry_date={{$entry_date}}"  class="btn btn-success select-md">Export CSV</a>                        
                    @endif
                </div>
                
            </div>
        </div>                     
    </div>
    </form>  

    <div class="row" id="myTable">
        <div class="col-sm-12">    
            <div class="table-responsive">
            <table class="table table-sm table-hover ledger">
                <thead>
                    <tr> 
                        <th>#</th>
                        <th>Product</th>
                        <th>Purpose</th>
                        <th>From / To</th>
                        <th>Rate</th>
                        <th>In / Out (Ctns)</th>
                    </tr>
                </thead>
                <tbody id="ledger_body">
                    @php
                        
                        $i=1;
                    @endphp
                      
                    @forelse ($data as $item)
                    @php
                        $purpose = $particular = "";
                        $in_quantity = $out_quantity = '';
                        $userName = "";
                        $ctnSpanClassname = "";
                        if($item->type == 'in'){
                            $ctnSpanClassname = "text-success";
                            $purpose = "GOODS RECEIVED";
                            $particular = "GRN / ".$item->stock->grn_no;
                            if(!empty($item->stock->purchase_order->supplier)){
                                $userName = 'SUPPLIER:- '.$item->stock->purchase_order->supplier->name;
                            } else {
                                $userName = 'STORE:- '.$item->stock->returns->store->bussiness_name;
                            }
                        } 
                        if($item->type == 'out'){
                            $ctnSpanClassname = "text-danger";
                            $purpose = "GOODS DISBURSED";
                            if(!empty($item->packingslip_id)){
                                $particular = "PACKING SLIP / ".$item->packingslip->slipno;
                                $particular = "PACKING SLIP / ".$item->packingslip->slipno;
                                $userName = 'STORE:- '.$item->packingslip->store->bussiness_name;
                            }
                            if(!empty($item->purchase_return_id)){
                                $particular = "PURCHASE RETURN / ".$item->purchase_return->order_no;
                                $particular = "PURCHASE RETURN / ".$item->purchase_return->order_no;
                                $userName = 'SUPPLIER:- '.$item->purchase_return->supplier->name;
                            }
                        }
                        
                    @endphp
                    <tr class="store_details_row">
                        <td>{{$i}}</td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $purpose }}</td>
                        <td>{{ $userName }}</td>
                        <td>Rs. {{ number_format((float)$item->piece_price, 2, '.', '') }}</td>
                        <td>
                            <span class="{{$ctnSpanClassname}}">{{ $item->quantity }}</span>
                        </td>
                       
                        
                    </tr>   
                    <tr>
                        <td colspan="6" class="store_details_column">
                            <div class="store_details">
                                <table class="table">
                                    <tr>
                                        <td><strong> {{ $particular }}</strong></td>
                                        <td>Date: {{ date('d/m/Y', strtotime($item->entry_date)) }}</td>
                                    </tr>
                                    <tr>                                       
                                        @php
                                            $purpose_name = "";
                                        @endphp
                                        @if ($item->type == 'in')
                                        @php
                                            $receiver_name = "";
                                            if(!empty($item->stock->purchase_order)){
                                                $receiver_name = $item->stock->purchase_order->supplier->name ;
                                                $purpose_name = "Goods Received From Supplier";
                                            } else {
                                                $receiver_name = $item->stock->returns->store->bussiness_name;
                                                $purpose_name = "Goods Returned From Store";
                                            }
                                        @endphp
                                        <td><span>Purpose: {{$purpose_name}}</span></td>
                                        <td><span>From: 
                                            {{ $receiver_name }} </span>
                                        </td>
                                        @else
                                        @php
                                            $purpose_name = "Goods Disbursed To Store";

                                            if (!empty($item->purchase_return_id)) {
                                                $purpose_name = "Goods Returned To Supplier";
                                                $to_name = $item->purchase_return->supplier->name;
                                            } else {
                                                $to_name = $item->packingslip->store->bussiness_name;
                                            }
                                        @endphp
                                        <td><span>Purpose: {{ $purpose_name }} </span></td>
                                        <td><span>To:  {{ $to_name }}</span></td>
                                        @endif                                        
                                        <td><span>Rate:  Rs. {{ number_format((float)$item->piece_price, 2, '.', '') }}</span></td>
                                        <td><span>Total:  Rs. {{ number_format((float)$item->total_price, 2, '.', '') }}</span></td>
                                    </tr>
                                    
                                    
                                </table>
                            </div>
                        </td>
                    </tr> 
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No record found</td>
                    </tr>    
                    @endforelse                                    
                </tbody>
            </table>    
            </div>
        </div>        
    </div> 
</section>
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);        
    });

    $('#type').on('change', function(){
        $('#ledgerForm').submit();
    });    
    
    $("[type='date']").bind('keyup keydown',function (evt) {
        evt.preventDefault();
        alert('Please choose date by clicking on calender icon');
    });

    $('.dates').on('change', function(){  
        $('#ledgerForm').submit();
    });  
    
    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        
        $('#searchProId').val('');
        $('#searchForm').submit();
    });

    function getProductByName(name) {  
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.product.searchByName') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: name
                },
                success: function(result) {
                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 product-dropdown select-md" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchProduct(${value.id})">${value.name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 product-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No product found</li></div>`;
                    }
                    $('#respDrop').html(content);
                }
            });
        }   else {
            $('.product-dropdown').hide()
        }   
        
    }

    function fetchProduct(id) {
        $('.product-dropdown').hide()
        $.ajax({
            url: "{{ route('admin.product.viewDetail') }}",
            method: 'post',
            data: {
                '_token': '{{ csrf_token() }}',
                id: id
            },
            success: function(result) {
                console.log(result);
                var name = result.name;
                $('#searchProId').val(id);
                $('#searchProText').val(name);
                $('#ledgerForm').submit();
            }
        });                
    }
    
</script>
@endsection
