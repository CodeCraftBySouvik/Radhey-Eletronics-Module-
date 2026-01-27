@extends('admin.layouts.app')
@section('page', 'Sales Analysis')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li><a href="{{ route('admin.report.sales-analysis') }}">Sales Analysis</a> </li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="ledgerForm">
    <div class="row">       
        <div class="col-12" id="single_product_form">
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
                <div class="col-auto ">
                    <a  class="btn btn-outline-success " id="getProductBtn">Filter Product</a>
                    <a href="{{route('admin.report.sales-analysis')}}" class="btn btn-outline-warning">Reset</a>
                </div> 
                @if (!empty($product_ids))
                <div class="col-auto">
                    <a href="javascript:void(0)" onclick="downloadLedger('excel');" class="btn btn-success ">Export Excel</a>
                </div>
                @endif
                
                  
                <div class="col-auto ms-auto">
                    <a  class="btn btn-outline-success " id="getStoreBtn">Filter Stores</a>
                </div>  
                @if (!empty($store_ids) && !empty($product_ids))
                
                <div class="col-auto">
                    <a class="btn btn-warning " id="resetStoreBtn" href="{{ route('admin.report.sales-analysis') }}?from_date={{$from_date}}&to_date={{$to_date}}&proidc={{$proidc}}">Reset Stores</a>
                </div>  
                @endif
                
                
            </div>
        </div> 
        <div class="col-12" id="proDiv">
            <div class="row g-3 align-items-end">
                
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="">Product </label>
                        <input type="search" name="" placeholder="Please Search Product" class="form-control  mb-0" value="" id="searchProText" autocomplete="off" onkeyup="getProductByName(this.value);">   
                    </div>
                    <div class="respDrop" id="respDrop"></div>
                </div> 
                <div class="col-auto" >
                   <input type="hidden" name="type" id="form_type" value="1">
                    <button type="submit" class="btn btn-success">Submit</button>
                    <button type="button" class="btn btn-outline-warning" onclick="fetchAllProduct()" id="fetct_all_product">Fetch All Product</button>
                </div>                  
            </div>
            <div class="row ">
                <ul class="stores_class" id="product_ul">
                    @if(!empty($product_ids))
                         @foreach ($product_ids as $products )
                            <li id="productli_{{$products}}"> 
                                {{getSingleAttributeTable('products',$products,'name')}} 
                                <a href="javascript:void(0);" onclick="removeProduct({{$products}});"><i class="fa fa-close"></i>
                                </a> 
                                <input type="hidden" class="product_ids" name="product_ids[]" value="{{$products}}" >
                            </li>
                        @endforeach 
                    @endif
                </ul>                
            </div>
        </div>
        <div class="col-12" id="storeDiv">
            <div class="row g-3 align-items-end">
                
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="">Store </label>
                        <input type="search" name="" placeholder="Please Search Store" class="form-control  mb-0" value="" id="searchStoreText" autocomplete="off" onkeyup="getStoreByName(this.value);">   
                    </div>
                    <div class="respDropStore" id="respDropStore"></div>
                </div> 
                <div class="col-auto" >
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>                  
            </div>
            <div class="row ">
                <ul class="stores_class" id="store_ul">
                    @if(!empty($store_ids))
                    @forelse ( $store_ids as $stores )
                    <li id="storeli_{{$stores}}"> 
                        {{getSingleAttributeTable('stores',$stores,'bussiness_name')}} 
                        <a href="javascript:void(0);" onclick="removeStore('{{$stores}}');"><i class="fa fa-close"></i>
                        </a> 
                        <input type="hidden" class="store_ids" name="store_ids[]" value="{{$stores}}" >
                    </li>
                    @empty
                        
                    @endforelse 
                    @endif
                        
                   
                    
                    
                </ul>                
            </div>
        </div>                      
    </div>
    </form>
     <form action="{{ route('admin.report.all-product-sales-analysis') }}" method="POST">
        @csrf
        <input type="hidden" name="from_date" value="{{$from_date}}">
        <input type="hidden" name="to_date" value="{{$to_date}}">
        <div id="all_product_form">
            
        </div>
    </form>
    @if (!empty($product_ids))
        <div class="row" id="myTable">
        <div class="col-sm-12">    
            <div class="table-responsive">
            {{-- <table class="table table-sm table-hover ledger">
                <thead>
                    <tr> 
                        <th>#</th>
                        <th>Date</th>
                        <th>Product</th>
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
                    @forelse ($data as $item)
                    @php
                        $total_price = ($item->pcs * $item->single_product_price);
                    @endphp
                    <tr>
                        <td>{{$i}}</td>
                        <td>{{ date('d/m/Y', strtotime($item->created_at)) }}</td>
                        <td>{{ $item->product->name }}</td>
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
            </table>     --}}
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
                
                <div>
                    
                </div>
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

    $(document).ready(function(){        
        $('div.alert').delay(3000).slideUp(300);
        // var searchProId = $('#searchProId').val();
        // if(searchProId == ''){
        //     $('#myTable').hide();
        // }
        
        @if (!empty($store_ids))
            $('#storeDiv').show();
        @else
            $('#storeDiv').hide();
        @endif

        @if (!empty($product_ids))
            $('#proDiv').show();
            $('#myTable').show();
        @else
            $('#proDiv').hide();
            $('#myTable').hide();
        @endif
        

        console.log(storeIdArr);

        $('.store_ids').each(function(){ 
            storeIdArr.push($(this).val())
        });
        $('.product_ids').each(function(){ 
            proIdArr.push($(this).val())
        });
    })
    $('#getStoreBtn').on('click', function(){
        if($('#storeDiv').is(':hidden')) {
            $('#storeDiv').show();
        } else {
            $('#storeDiv').hide();
        }
    })
    $('#getProductBtn').on('click', function(){
        if($('#proDiv').is(':hidden')) {
            $('#proDiv').show();
        } else {
            $('#proDiv').hide();
        }
    })
    function downloadLedger(e){        
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();
        
        var dataString = "from_date="+from_date+"&to_date="+to_date+"&proidc={{$proidc}}&storeidc={{$storeidc}}" ;
        
        if(e == 'excel'){
            window.location.href = "{{ route('admin.report.sales-analysis-csv') }}?"+dataString; 
        }
        
    }
    
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
        
        // $('#searchProId').val('');
        // $('#searchForm').submit();
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
                var name = result.name;                
                proIdArr.push(id);
                $('#product_ul').append(`<li id="productli_`+id+`">`+name+` <a href="javascript:void(0);"  onclick="removeProduct('`+id+`');"><i class="fa fa-close"></i></a><input type="hidden" class="product_ids" name="product_ids[]" value="`+id+`" ></li>`);
                $('.product-dropdown').hide();
                $('#searchProId').val('');
                $('#searchProText').val('');
            }
        });                
    }
   function fetchAllProduct() {
        $('#all_product_form').html(''); // Clear the product list
        $('#app-preloader').removeClass('fade');
        
        $('#fetct_all_product').text('Please wait...').prop('disabled', true);
        $.ajax({
            url: "{{ route('admin.product.viewAllDetail') }}",
            method: 'post',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            success: function(result) {
                console.log(result);
                $('#form_type').val(2);
                // Clear the existing list before adding new items
                $('#all_product_form').html('');
                $('#proDiv').addClass('d-none');
                if(result.length==0){
                    $('#all_product_form').append(`
                     <ul class="stores_class">
                        <li>Data Not Found!</li>
                    </ul>
                    `);
                }else{
                    $('#all_product_form').append(`
                        <div class="row">
                        <div class="col-auto">
                    `);
    
                    // Display the total number of products
                    $('#all_product_form').append(`
                     <ul class="stores_class">
                        <li>Total Products: ` + result.length + ` <button type="submit" class="btn btn-success" style="margin-left: 17px;border-radius: 2px 11px 9px 2px;">Submit</button></li>
                    </ul>
                    `);
                    // Iterate over the result (array of products)
                    result.forEach(function(product) {
                        var id = product.id;
                        $('#app-preloader').addClass('fade');
                        $('#fetct_all_product').text('Fetch All Product').prop('disabled', false);
                        proIdArr.push(id); // Add the product ID to the array
                
                        // Append each product to the list
                        $('#all_product_form').append(`
                            <li id="productli_` + id + `" style="display:none;">
                                <input type="hidden" class="product_ids" name="product_ids[]" value="` + id + `">
                            </li>
                        `);
                    });  
                }
            
                
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

    

    function getStoreByName(name){
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.ledger.storeSearch') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    search: name,
                    idnotin: storeIdArr
                },
                success: function(result) {
                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show store-dropdown select-md" aria-labelledby="dropdownMenuButton" style="width: 491px;">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchStore(${value.id},'${value.bussiness_name}')">${value.bussiness_name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 store-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No store found</li></div>`;
                    }
                    $('#respDropStore').html(content);
                }
            });
        }   else {
            $('.store-dropdown').hide()
        } 
    }

    function fetchStore(id,name){        
        storeIdArr.push(id);        
        $('#store_ul').append(`<li id="storeli_`+id+`">`+name+` <a href="javascript:void(0);"  onclick="removeStore('`+id+`');"><i class="fa fa-close"></i></a><input type="hidden" class="store_ids" name="store_ids[]" value="`+id+`" ></li>`);
        $('.store-dropdown').hide();
        $('#searchStoreText').val('');
        
    }

    function removeStore(id){
        // alert(id)
        console.log(storeIdArr);
        $('#store_ul > #storeli_'+id).remove();
        storeIdArr =  storeIdArr.filter(e => e!=id);
        console.log(storeIdArr);
    }
    
</script>
@endsection
