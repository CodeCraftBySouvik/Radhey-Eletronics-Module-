@extends('admin.layouts.app')
@section('page', 'Stock Ledger')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li><a href="{{ route('admin.report.stock-ledger') }}">Stock Ledger</a> </li>
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
                        <label for="">From</label>
                        <input type="date" name="from_date"  id="from_date" class="form-control  dates" value="{{ $from_date }}" @if(!empty($is_opening_bal)) min="{{ $opening_bal_date }}" @endif  max="{{ $to_date }}" min="{{$min_from_date}}" placeholder="From" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">To</label>
                        <input type="date" name="to_date"  id="to_date" class="form-control  dates" value="{{ $to_date }}" placeholder="To" max="{{ date('Y-m-d') }}" min="{{ $from_date }}" autocomplete="off">  
                    </div>
                </div>
                {{-- <div class="col-sm-4">
                    <div class="form-group">
                        <label for="" id="lable_user">
                            Product                  
                        </label> 
                        <span class="text-danger">*</span>
                        <input type="text" name="product_name" placeholder="Please Search Product First" class="form-control select-md" value="{{$product_name}}" id="searchProText" onkeyup="getProductByName(this.value);" autocomplete="off">      
                        <input type="hidden" name="product_id" id="searchProId" value="{{$product_id}}">
                                        
                    </div>
                    <div class="respDrop" id="respDrop"></div>
                </div>    --}}

                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="">Product </label>
                        <input type="text" name="" placeholder="Please Search Product" class="form-control " value="" id="searchProText" autocomplete="off" onkeyup="getProductByName(this.value);">   
                    </div>
                    <div class="respDrop" id="respDrop"></div>
                </div> 
                <div class="col-auto" >
                    <button type="submit" class="btn btn-success ">Submit</button>
                </div>  
                @if (!empty($product_ids))
                <div class="col-auto">
                    <a href="{{ route('admin.report.stock-ledger') }}?from_date={{$from_date}}&to_date={{$to_date}}" class="btn btn-outline-warning">Reset Product</a>
                </div>
                <div class="col-auto">
                    <a href="javascript:void(0)" onclick="downloadLedger('csv');" class="btn btn-outline-success">Export CSV</a>
                </div>
                @endif
                
            </div>
        </div>
        <div class="col-12" id="proDiv">
            {{-- <div class="row g-3 align-items-end">
                
                                
            </div> --}}
            <div class="row ">
                <ul class="stores_class" id="product_ul">
                    @if(!empty($product_ids))
                    @forelse ( $product_ids as $products )
                    <li id="productli_{{$products}}"> 
                        {{getSingleAttributeTable('products',$products,'name')}} 
                        <a href="javascript:void(0);" onclick="removeProduct({{$products}});"><i class="fa fa-close"></i>
                        </a> 
                        <input type="hidden" class="product_ids" name="product_ids[]" value="{{$products}}" >
                    </li>
                    @empty
                        
                    @endforelse 
                    @endif
                        
                   
                    
                    
                </ul>                
            </div>
        </div>
    </div>
    </form>  
    @if (!empty($product_ids))
        
    
    <div class="row" id="myTable">
        <div class="col-sm-12">    
            <div class="table-responsive">
            {{-- <table class="table table-sm table-hover ledger">
                <thead>
                    <tr> 
                        <th>Date</th>
                        <th>Purpose</th>
                        <th>Supplier / Store</th>
                        <th>Rate</th>
                        <th>In (Ctns)</th>
                        <th>Out (Ctns)</th>
                        <th>Closing (Ctns)</th>
                    </tr>
                </thead>
                <tbody id="ledger_body">
                    @php
                        $net_quantity = $net_in = $net_out = 0; 
                        if(!empty($opening_stock)){
                            $net_quantity += $opening_stock;
                        }
                    @endphp
                    <tr>
                        <td>{{ date('d/m/Y', strtotime($from_date)) }}</td>
                        <td colspan="">OPENING BALANCE</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            {{$opening_stock}}
                        </td>
                    </tr>   
                    @forelse ($data as $item)
                    @php
                        $purpose = $particular = "";
                        $in_quantity = $out_quantity = '';
                        $userName = "";
                        if($item->type == 'in'){
                            $net_in += $item->quantity;
                            $in_quantity = $item->quantity;
                            $net_quantity += $item->quantity;
                            $purpose = "GOODS RECEIVED";
                            $particular = "GRN / ".$item->stock->grn_no;
                            if(!empty($item->stock->purchase_order->supplier)){
                                $userName = 'SUPPLIER:- '.$item->stock->purchase_order->supplier->name;
                            } else {
                                $userName = 'STORE:- '.$item->stock->returns->store->bussiness_name;
                            }
                        } 
                        if($item->type == 'out'){
                            $net_out += $item->quantity;
                            $out_quantity = $item->quantity;
                            $net_quantity -= $item->quantity;
                            $purpose = "GOODS DISBURSED";
                            if(!empty($item->packingslip)){
                                $particular = "PACKING SLIP / ".$item->packingslip->slipno;
                                $userName = 'STORE:- '.$item->packingslip->store->bussiness_name;
                            } else if (!empty($item->purchase_return)) {
                                $particular = "PURCHASE RETURN / ".$item->purchase_return->order_no;
                                $userName = 'SUPPLIER:- '.$item->purchase_return->supplier->name;
                            }
                            
                        }
                        
                    @endphp
                    <tr class="store_details_row">
                        <td>{{ date('d/m/Y', strtotime($item->entry_date)) }}</td>
                        <td>{{ $purpose }}</td>
                        <td>{{$userName}}</td>
                        <td>Rs. {{ number_format((float)$item->piece_price, 2, '.', '') }}</td>
                        <td>
                            <span class="text-success">{{ $in_quantity }}</span>
                        </td>
                        <td>
                            <span class="text-danger">{{ $out_quantity }}</span>
                        </td>
                        <td>
                            {{$net_quantity}}
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
                                            $purpose_name = "";
                                            
                                            if(!empty($item->packingslip)){
                                                $to_name = $item->packingslip->store->bussiness_name ;
                                                $purpose_name = "Goods Disbursed To Store";
                                            } else if (!empty($item->purchase_return)) {
                                                $to_name = $item->purchase_return->supplier->name ;
                                                $purpose_name = "Goods Returned To Supplier";
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
                    @empty

                    @endforelse

                    @if(!empty($product_id))
                    <tr class="table-info">
                        <td colspan="4"><strong>Closing Stock</strong>  </td>
                        <td>
                            @if (!empty($net_in))
                            <strong>{{$net_in}}</strong>
                            
                            @endif                            
                        </td>
                        <td>
                            @if (!empty($net_out))
                            <strong>{{$net_out}}</strong>
                           

                            @endif                            
                        </td>
                        <td>                            
                            <strong>                                                               
                                {{ $net_quantity }}
                            </strong>
                        </td>
                    </tr>  
                    @endif
                                    
                </tbody>
            </table>     --}}
            <table class="table table-sm table-hover ledger">
                @forelse ($data as $product_id => $products) 
                
                <thead>
                    <tr>
                        <th colspan="8">{{getSingleAttributeTable('products',$product_id,'name')}}</th>
                    </tr>
                    <tr> 
                        <th>Date</th>
                        <th>Purpose</th>
                        <th>Supplier / Store</th>
                        <th>Rate</th>
                        <th>In (Ctns)</th>
                        <th>Out (Ctns)</th>
                        <th>Closing (Ctns)</th>
                    </tr>
                </thead>
                
                <div>
                    
                </div>
                <tbody id="ledger_body">
                    @php
                        $opening_stock = openingStock($product_id,$from_date);
                        // dd($opening_stock);
                        $net_quantity = $net_in = $net_out = 0; 
                        if(!empty($opening_stock)){
                            $net_quantity += $opening_stock;
                        }
                    @endphp
                    <tr>
                        <td>{{ date('d/m/Y', strtotime($from_date)) }}</td>
                        <td colspan="">OPENING BALANCE</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            {{$opening_stock}}
                        </td>
                    </tr>  
                    @forelse ($products as $item)
                    @php
                        $purpose = $particular = "";
                        $in_quantity = $out_quantity = '';
                        $userName = "";
                        if($item->type == 'in'){
                            $net_in += $item->quantity;
                            $in_quantity = $item->quantity;
                            $net_quantity += $item->quantity;
                            $purpose = "GOODS RECEIVED";
                            $particular = "GRN / ".$item->stock->grn_no;
                            if(!empty($item->stock->purchase_order->supplier)){
                                $userName = 'SUPPLIER:- '.$item->stock->purchase_order->supplier->name;
                            } else {
                                $userName = 'STORE:- '.$item->stock->returns->store->bussiness_name;
                            }
                        } 
                        if($item->type == 'out'){
                            $net_out += $item->quantity;
                            $out_quantity = $item->quantity;
                            $net_quantity -= $item->quantity;
                            $purpose = "GOODS DISBURSED";
                            if(!empty($item->packingslip)){
                                $particular = "PACKING SLIP / ".$item->packingslip->slipno;
                                $userName = 'STORE:- '.$item->packingslip->store->bussiness_name;
                            } else if (!empty($item->purchase_return)) {
                                $particular = "PURCHASE RETURN / ".$item->purchase_return->order_no;
                                $userName = 'SUPPLIER:- '.$item->purchase_return->supplier->name;
                            }
                            
                        }
                        
                    @endphp
                    <tr class="store_details_row">
                        <td>{{ date('d/m/Y', strtotime($item->entry_date)) }}</td>
                        <td>{{ $purpose }}</td>
                        <td>{{$userName}}</td>
                        <td>Rs. {{ number_format((float)$item->piece_price, 2, '.', '') }}</td>
                        <td>
                            <span class="text-success">{{ $in_quantity }}</span>
                        </td>
                        <td>
                            <span class="text-danger">{{ $out_quantity }}</span>
                        </td>
                        <td>
                            {{$net_quantity}}
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
                                            $purpose_name = "";
                                            
                                            if(!empty($item->packingslip)){
                                                $to_name = $item->packingslip->store->bussiness_name ;
                                                $purpose_name = "Goods Disbursed To Store";
                                            } else if (!empty($item->purchase_return)) {
                                                $to_name = $item->purchase_return->supplier->name ;
                                                $purpose_name = "Goods Returned To Supplier";
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
                    @empty

                    @endforelse    
                    <tr class="table-info">
                        <td colspan="4"><strong>Closing Stock</strong>  </td>
                        <td>
                            @if (!empty($net_in))
                            <strong>{{$net_in}}</strong>
                            
                            @endif                            
                        </td>
                        <td>
                            @if (!empty($net_out))
                            <strong>{{$net_out}}</strong>
                           

                            @endif                            
                        </td>
                        <td>                            
                            <strong>                                                               
                                {{ $net_quantity }}
                            </strong>
                        </td>
                    </tr>                     
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
    var proIdArr = [];
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
        

        $('.product_ids').each(function(){ 
            proIdArr.push($(this).val())
        });
    })
    function downloadLedger(e){        
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();

        var dataString = "from_date="+from_date+"&to_date="+to_date+"&proidc={{$proidc}}" ;
        
        if(e == 'csv'){
            window.location.href = "{{ route('admin.report.stock-ledger-csv') }}?"+dataString; 
        }
        
    }
    
    $("[type='date']").bind('keyup keydown',function (evt) {
        evt.preventDefault();
        alert('Please choose date by clicking on calender icon');
    });

    $('.dates').on('change', function(){  
        $('#ledgerForm').submit();
    }); 
    
    $('#getProductBtn').on('click', function(){
        if($('#proDiv').is(':hidden')) {
            $('#proDiv').show();
        } else {
            $('#proDiv').hide();
        }
    })
    
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
                    term: name,
                    idnotin: proIdArr
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
                proIdArr.push(id);
                var name = result.name;     
                $('#product_ul').append(`<li id="productli_`+id+`">`+name+` <a href="javascript:void(0);"  onclick="removeProduct('`+id+`');"><i class="fa fa-close"></i></a><input type="hidden" class="product_ids" name="product_ids[]" value="`+id+`" ></li>`);
                $('.product-dropdown').hide();
                $('#searchProId').val('');
                $('#searchProText').val('');
            }
        });                
    }

    function removeProduct(id){
        // alert(id)
        console.log(proIdArr);
        $('#product_ul > #productli_'+id).remove();
        proIdArr =  proIdArr.filter(e => e!=id);
        console.log(proIdArr);
    }
    
</script>
@endsection
