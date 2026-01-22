@extends('admin.layouts.app')
@section('page', 'Price Requests')
@section('content')
<section>
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="searchForm">        
    <div class="search__filter">
        <div class="row align-items-end justify-content-between">
            <div class="col">
                
            </div>
            <div class="col-4">
                <div class="form-group">
                    <input type="text" name="product_name" placeholder="Please Search Product ... " class="form-control select-md" value="{{$product_name}}" id="searchProText" onkeyup="getProductByName(this.value);">      
                    <input type="hidden" name="product_id" id="searchProId" value="{{$product_id}}">
                    <div class="respDrop" id="respDrop"></div>
                </div>
            </div> 
            <div class="col-4">
                <div class="form-group">
                    <input type="text" name="store_name" class="form-control select-md" id="store_name" placeholder="Please Search Store ... " onkeyup="getStores(this.value);" value="{{ $store_name }}" >
                    <input type="hidden" name="store_id" id="store_id" value="{{ $store_id }}">
                    <div class="respDropStore" id="respDropStore" style="position: relative;"></div>
                </div>
            </div> 
            
            <div class="col-auto">
                <a href="{{ route('admin.threshold.list') }}?search={{$search}}" class="btn btn-outline-warning select-md">Reset</a>
            </div> 
            
                  
        </div>
        
    </div>
    <div class="search__filter">
        <div class="row align-items-end justify-content-between">
            <div class="col">
                
            </div>
            
            <div class="col-4">
                <div class="form-group">
                    <input type="search" name="search" class="form-control select-md" id="search" placeholder="Search By Request Id or Master Order Id"  value="{{ $search }}" >
                   
                </div>
            </div> 
            <div class="col-auto">
                <a href="{{ route('admin.threshold.list') }}?product_id={{$product_id}}&product_name={{$product_name}}&store_id={{$store_id}}&store_name={{$store_name}}" class="btn btn-outline-warning select-md">Reset</a>
            </div> 
            
                  
        </div>
        
    </div>
    <div class="filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                <div class="row g-3 align-items-center">                            
                </div>
            </div>
            <div class="col-auto">                        
                <p>Total {{$total}} Records</p>
            </div>
        </div>
    </div>
    </form>
    <div class="row">
        <div class="col-sm-12">  
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>Placed By</th>
                            <th>Store</th>
                            <th>Product</th>
                            <th>Threshold Price (Inc.Tax)</th>
                            <th>Requested Price (Price per PC)</th>
                            <th>Requested Quantity</th>
                            <th>Action</th>
                            <th>Master Order</th>
                            <th>Order Status</th>                         
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i=1;
                        @endphp
                        @forelse ($data as $index => $item)

                        @php
                            if(empty($item->is_approved)){
                                $statusText = "Yet to Approve";
                                $statusClass = "badge bg-warning";
                            }else if($item->is_approved == 1){
                                $statusText = "Approved";
                                $statusClass = "badge bg-success";
                            }else if($item->is_approved == 2){
                                $statusText = "Denied";
                                $statusClass = "badge bg-danger";
                            }
                            $piece_price = ($item->price / $item->pcs);
                        @endphp
                        <tr>
                            <td>{{ $i }}</td>   
                            <td>
                                <strong>{{ $item->unique_id }}</strong>
                                                                
                            </td>  
                            <td>
                                <span> {{ date('j M Y - h:i A, l', strtotime($item->created_at))}} </span> <br/>
                            </td>           
                            <td>
                                <strong>{{ $item->user->name }}</strong> <br/>
                                @if (!empty($item->hold_order_id))
                                    <span class="">( From Web )</span>
                                @else
                                    <span class="">( From App )</span>
                                @endif
                                
                            </td>
                            <td>{{ !empty($item->store->bussiness_name)?$item->store->bussiness_name:$item->store->store_name }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ 'Rs. '.number_format((float)$item->sell_price, 2, '.', '') }}</td>
                            <td>
                                {{ 'Rs. '.number_format((float)$piece_price, 2, '.', '') }}
                            </td>
                            <td>{{ $item->qty }} ctns ( {{$item->qty*$item->pcs}} pcs )</td>
                            <td>
                                @if(empty($item->is_approved))
                                    <a href="{{ route('admin.threshold.view', $item->id) }}" class="btn btn-outline-primary select-md">Approve</a> 
                                @else
                                    <span class="{{ $statusClass }}">{{ $statusText }}</span>   <br/>

                                    <span> {{ date('j M Y - h:i A, l', strtotime($item->updated_at))}} </span> <br/>
                                @endif
                            </td>
                            
                            <td>
                                @if (!empty($item->hold_order))
                                    <a href="{{ route('admin.order.view', $item->hold_order_id) }}" class="btn btn-outline-success select-md" title="Click to view order details"> {{ $item->hold_order->order_no }}</a>
                                @endif
                            </td>
                            <td>
                                @if (empty($item->is_approved))
                                    <span class="badge bg-warning">Yet to Receive</span>
                                @elseif ($item->is_approved == 1)

                                    @if (empty($item->hold_order_id))
                                            @if (empty($item->customer_approval) && ($item->customer_approval != 2) )
                                            <a href="{{ route('admin.threshold.view-requested-price-received-order', $item->id) }}" class="btn select-md btn-outline-primary">Place Order</a>  
                                        @else
                                            @php
                                                $customer_approval_text = "Received";
                                                $customer_approval_class = "success";
                                            @endphp 
                                            <span class="badge bg-{{$customer_approval_class}}">{{ $customer_approval_text }}</span> <br/> <br/>
                                            <a href="{{ route('admin.order.view', $item->order_id) }}" title="View Order" class="btn btn-outline-secondary select-md">Order No: {{$item->order_no}} </a>
                                            
                                        @endif
                                    @else
                                        <span class="badge bg-info">Item Attached With Master Order </span>
                                    @endif
                                    
                                @else
                                    <span class="badge bg-danger">Denied Request</span>
                                @endif
                                    
                                
                                
                            </td>                   
                        </tr>
                        @php
                            $i++;
                        @endphp

                        @empty
                        <tr><td colspan="100%" class="small text-muted text-center">No data found</td></tr>
                        @endforelse
                    </tbody>
                </table> 
                {{ $data->links() }}
            </div>
        </div>        
    </div>
</section>
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    })  

    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });


    function getStores(val){
        if(val.length > 0){
            $.ajax({
                url: "{{ route('admin.ledger.getUsersByType') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: val,
                    type: 'store'
                },
                success: function(result) {

                    console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 user-dropdown select-md" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {                        
                            if(value.bussiness_name != ''){
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.bussiness_name}')">${value.bussiness_name}</a>`;
                            } else {
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}')">${value.name}</a>`;
                            }                        
                            
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 user-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No store found</li></div>`;
                    }
                    $('.respDropStore').html(content);
                }
            });
        } else {
            $('.respDropStore').text('');
            $('#store_id').val(0);
            $('#store_name').val('');
        }
        
    }

    function fetchCode(id,name) {
        $('.user-dropdown').hide()
        $('input[name="store_id"]').val(id)
        $('input[name="store_name"]').val(name)
        $('#searchForm').submit();        
    }

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
                $('#searchForm').submit();
            }
        });                
    }
    
</script>
@endsection
