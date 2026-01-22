@extends('admin.layouts.app')
@section('page', 'Payment Collection Report')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Payment Collection </li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="reportForm">
        {{-- <input type="hidden" name="stores" value="{{$storeidc}}"> --}}
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
            @if (Auth::user()->designation == NULL)
            <div class="col-sm-1">
                <div class="form-group">
                    <label for="">Bank / Cash</label>
                    <select name="bank_cash" onchange="this.form.submit();" id="bank_cash" class="form-control select-md dates">
                        <option value="" @if(empty($bank_cash)) selected @endif>All</option>
                        <option value="bank" @if($bank_cash == 'bank') selected @endif>Bank</option>
                        <option value="cash" @if($bank_cash == 'cash') selected @endif>Cash</option>
                    </select> 
                </div>
            </div>
            @else
            <input type="hidden" name="bank_cash" value="bank">
            @endif

            <div class="col-sm-1">
                <div class="form-group">
                    <label for="">Filter</label>

                    @if (!empty($store_ids) || !empty($city_ids)) 
                    <input type="hidden" name="filter_by" value="{{$filter_by}}">
                    
                    @endif
                    
                    <select name="filter_by" id="filter_by" @if (!empty($store_ids) || !empty($city_ids)) disabled @endif class="form-control select-md dates">
                        <option value="" @if(empty($filter_by)) selected @endif>All</option>
                        <option value="store" @if($filter_by == 'store') selected @endif>Store</option>
                        <option value="city" @if($filter_by == 'city') selected @endif>City</option>
                    </select> 
                </div>
            </div>

            @if ($filter_by == 'store')
            <div class="col-auto me-auto">                    
                <a  class="btn btn-outline-success select-md" id="getStoreBtn">Filter Stores</a>
            
                <a class="btn btn-outline-warning select-md" id="resetStoreBtn" href="{{ route('admin.report.payment-receipt-report') }}?from_date={{$from_date}}&to_date={{$to_date}}&bank_cash={{$bank_cash}}&filter_by={{$filter_by}}">Reset Stores</a>
            </div>  
            @elseif ($filter_by == 'city')
            <div class="col-auto me-auto">                    
                <a  class="btn btn-outline-success select-md" id="getCityBtn">Filter Cities</a>
            
                <a class="btn btn-outline-warning select-md" id="resetCityBtn" href="{{ route('admin.report.payment-receipt-report') }}?from_date={{$from_date}}&to_date={{$to_date}}&bank_cash={{$bank_cash}}&filter_by={{$filter_by}}">Reset Cities</a>
            </div>                
            @endif            
            <div class="col-auto ms-auto">
                <a href="{{ route('admin.report.payment-receipt-report-csv') }}?from_date={{$from_date}}&to_date={{$to_date}}&storeidc={{$storeidc}}&citydc={{$citydc}}&bank_cash={{$bank_cash}}" class="btn btn-success select-md">Export CSV</a>
            </div>          
        </div>
        
    </div>
    <div class="col-12" id="cityDiv">
        <div class="row g-3 align-items-end">
            
            <div class="col-sm-4">
                <div class="form-group">
                    <label for=""> City </label>
                    <input type="text" name="" placeholder="Please Search City" class="form-control select-md mb-0" value="" id="searchCityText" onkeyup="getCityByName(this.value);" autocomplete="off">   
                </div>
                <div class="respDropCity" id="respDropCity"></div>
            </div> 
            <div class="col-auto" >
                <button type="submit" class="btn btn-outline-success select-md">Submit</button>
            </div>                  
        </div>
        <div class="row ">
            <ul class="city_class">
                @if(!empty($city_ids))
                @forelse ( $city_ids as $cities )
                <li id="cityli_{{$cities}}"> 
                    {{getSingleAttributeTable('cities',$cities,'name')}} 
                    <a href="javascript:void(0);" onclick="removeCity('{{$cities}}');"><i class="fa fa-close"></i>
                    </a> 
                    <input type="hidden" class="city_ids" name="city_ids[]" value="{{$cities}}" >
                </li>
                @empty
                    
                @endforelse 
                @endif
                    
               
                
                
            </ul>                
        </div>
    </div>
    <div class="col-12" id="storeDiv">
        <div class="row g-3 align-items-end">
            
            <div class="col-sm-4">
                <div class="form-group">
                    <label for=""> Store </label>
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
                <p>Total {{$count_data}} Records</p>            
            </div>
        </div>
    </div>
    <div class="filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                
            </div>
            <div class="col-auto">   
                <span>Total Amount <strong>Rs. {{ number_format((float)$sum_data, 2, '.', '') }}</strong></span>
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
                        <th>Voucher No</th>
                        <th>Store</th>
                        @if (Auth::user()->designation == NULL)                            
                        <th>Bank/Cash</th>
                        @endif
                        <th>Amount</th>
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
                    @forelse ($data as $item)
                    <tr class="store_details_row">
                        <td>{{$i}}</td>
                        <td>{{ date('d/m/Y', strtotime($item->entry_date)) }}</td>
                        <td>{{$item->transaction_id}}</td>
                        <td>
                            {{$item->store->bussiness_name}}
                        </td>
                        @if (Auth::user()->designation == NULL)
                        <td>
                            {{ucwords($item->bank_cash)}}
                        </td>                            
                        @endif
                        <td>Rs. {{ number_format((float)$item->transaction_amount, 2, '.', '') }}</td>
                    </tr>                   
                    @php
                        $i++;
                    @endphp
                    @empty
                        
                    @endforelse                    
                </tbody>
            </table>    
            {{$data->links()}}
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
    $('#paginate').change(function(){
        $('#reportForm').submit();
    })
    var storeIdArr = cityIdArr = [];
    $(document).ready(function(){
        @if (!empty($store_ids))
            $('#storeDiv').show();
        @else
            $('#storeDiv').hide();
        @endif

        @if (!empty($city_ids))
            $('#cityDiv').show();
        @else
            $('#cityDiv').hide();
        @endif

        $('.store_ids').each(function(){ 
            storeIdArr.push($(this).val())
        });
        $('.city_ids').each(function(){ 
            cityIdArr.push($(this).val())
        });
    });

    $('#filter_by').on('change', function(){
        $('#reportForm').submit();
    })
    $('#getStoreBtn').on('click', function(){
        if($('#storeDiv').is(':hidden')) {
            $('#storeDiv').show();
        } else {
            $('#storeDiv').hide();
        }
    });

    $('#getCityBtn').on('click', function(){
        if($('#cityDiv').is(':hidden')) {
            $('#cityDiv').show();
        } else {
            $('#cityDiv').hide();
        }
    });

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

    function getCityByName(name){
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.ledger.searchCities') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    search: name,
                    idnotin: cityIdArr
                },
                success: function(result) {
                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show city-dropdown select-md" aria-labelledby="dropdownMenuButton" style="width: 491px;">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCity(${value.id},'${value.name}')">${value.name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 city-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No city found</li></div>`;
                    }
                    $('#respDropCity').html(content);
                }
            });
        }   else {
            $('.city-dropdown').hide()
        } 
    }

    function fetchCity(id,name){        
        cityIdArr.push(id);        
        $('.city_class').append(`<li id="cityli_`+id+`">`+name+` <a href="javascript:void(0);"  onclick="removeCity('`+id+`');"><i class="fa fa-close"></i></a><input type="hidden" class="city_ids" name="city_ids[]" value="`+id+`" ></li>`);
        $('.city-dropdown').hide();
        $('#searchCityText').val('');
        
    }

    function removeCity(id){
        // alert(id)
        console.log(cityIdArr);
        $('.city_class > #cityli_'+id).remove();
        cityIdArr =  cityIdArr.filter(e => e!=id);
        console.log(cityIdArr);
    }
</script>
@endsection
