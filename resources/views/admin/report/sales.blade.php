@extends('admin.layouts.app')
@section('page', 'Sales Report')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Sales</li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="reportForm">
    <input type="hidden" name="stores" value="{{$storeidc}}">
    <div class="search__filter">
        <div class="row align-items-end justify-content-between">
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
            
            <div class="col-auto me-auto">
                <a  class="btn btn-outline-success select-md" id="getStoreBtn">Filter Stores</a>
                <a  class="btn btn-outline-warning select-md" id="" href="{{ route('admin.report.sales-report') }}?from_date={{$from_date}}&to_date={{$to_date}}">Reset Stores</a>
            </div>  
            <div class="col-auto ms-auto">
                <a href="{{ route('admin.report.sales-report-pdf') }}?from_date={{$from_date}}&to_date={{$to_date}}&storeidc={{$storeidc}}" class="btn btn-success select-md">Download PDF</a>
                <a href="{{ route('admin.report.sales-report-csv') }}?from_date={{$from_date}}&to_date={{$to_date}}&storeidc={{$storeidc}}" class="btn btn-success select-md">Export CSV</a>
            </div>          
        </div>
        
    </div>
    <div id="storeDiv">
        <div class="row g-3 align-items-end">                
            <div class="col-sm-4">
                <div class="form-group">
                    <label for="">Store </label> 
                    <input type="text" name="" placeholder="Please Search Store" class="form-control select-md mb-0" value="" id="searchStoreText" onkeyup="getStoreByName(this.value);" autocomplete="off">   
                </div>
                <div class="respDropStore" id="respDropStore"></div>
            </div> 
            <div class="col-auto" >
                <button type="submit" class="btn btn-outline-success select-md">Submit</button>
            </div>                  
        </div>
        <div class="row ">
            <ul class="stores_class">
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
                <p>Total {{$count_order}} Records</p>            
            </div>
        </div>
    </div> 
    <div class="filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                
            </div>
            <div class="col-auto">   
                <span>Total Amount <strong>Rs. {{ number_format((float)$total_amount, 2, '.', '') }}</strong></span>
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
                        <th>Date</th>
                        <th>Order No / Invoice No</th>
                        <th>Store</th>
                        <th>Amount</th>
                        {{-- <th>Status</th> --}}
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
                    @forelse ($orders as $order)
                    <tr class="store_details_row">
                        <td>{{$i}}</td>
                        <td>{{ date('d/m/Y', strtotime($order->created_at)) }}</td>
                        <td>{{$order->order->order_no}} / {{ $order->invoice_no }}</td>
                        <td>
                            {{$order->store->bussiness_name}}
                        </td>
                        <td>Rs. {{ number_format((float)$order->net_price, 2, '.', '') }}</td>
                        {{-- <td>
                            @if (!empty($order->packingslip))
                                @if (!empty($order->packingslip->is_disbursed))
                                    <span class="badge bg-success">DISBURSED</span>
                                @else
                                    <span class="badge bg-warning">YET TO DISBURSE</span>
                                @endif
                            @else
                                <span class="badge bg-warning">YET TO DISBURSE</span>
                            @endif
                        </td> --}}
                    </tr>
                    <tr>
                        <td colspan="6" class="store_details_column">
                            <div class="store_details">
                                <table class="table">
                                    <thead>
                                        <tr> 
                                            <th>#</th>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price per Pcs</th>
                                            {{-- <th>Price per Ctn</th> --}}
                                            <th>Total Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $j = 1;
                                        @endphp
                                        @forelse ($order->products as $item)
                                        @php
                                            // $total_price = ($item->pcs * $item->single_product_price);
                                        @endphp
                                        <tr>
                                            <td>{{$j}}</td>
                                            <td>{{$item->product_name}}</td>
                                            <td>{{$item->quantity}} ctns ({{$item->pcs}} pcs)</td>
                                            <td>Rs. {{ number_format((float)$item->single_product_price, 2, '.', '') }}</td>
                                            {{-- <td>Rs. {{ number_format((float)$item->price, 2, '.', '') }}</td> --}}
                                            <td>
                                                Rs. {{ number_format((float)$item->total_price, 2, '.', '') }}
                                            </td>
                                        </tr>
                                        @php
                                            $j++;
                                        @endphp
                                        @empty
                                            
                                        @endforelse
                                        
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                        
                    @endforelse                    
                </tbody>
            </table>    
            {{$orders->links()}}
            </div>
        </div>        
    </div>
</section>
<script>
    $('#paginate').change(function(){
        $('#reportForm').submit();
    })
    var storeIdArr = [];
    $(document).ready(function(){
        @if (!empty($store_ids))
            $('#storeDiv').show();
        @else
            $('#storeDiv').hide();
        @endif

        $('.store_ids').each(function(){ 
            storeIdArr.push($(this).val())
        });
    })
    $('#getStoreBtn').on('click', function(){
        if($('#storeDiv').is(':hidden')) {
            $('#storeDiv').show();
        } else {
            $('#storeDiv').hide();
        }
    })
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
        $('.stores_class').append(`<li id="storeli_`+id+`">`+name+` <a href="javascript:void(0);"  onclick="removeStore('`+id+`');"><i class="fa fa-close"></i></a><input type="hidden" class="store_ids" name="store_ids[]" value="`+id+`" ></li>`);
        $('.store-dropdown').hide();
        $('#searchStoreText').val('');
        
    }

    function removeStore(id){
        // alert(id)
        console.log(storeIdArr);
        $('.stores_class > #storeli_'+id).remove();
        storeIdArr =  storeIdArr.filter(e => e!=id);
        console.log(storeIdArr);
    }
</script>
@endsection
