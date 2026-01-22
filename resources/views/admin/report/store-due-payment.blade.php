@extends('admin.layouts.app')
@section('page', 'Store Due Payments')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li><a href="{{ route('admin.report.store-due-payment') }}">Store Due Payments</a> </li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="ledgerForm">
    <div class="row">       
        <div class="col">
            <div class="row g-3 align-items-end">                
                <div class="col-sm-8">
                    <div class="form-group">
                        <label for="" id="lable_user">
                            Store                  
                        </label> 
                        <span class="text-danger">*</span>
                        <input type="search" name="bussiness_name" placeholder="Please Search Store" class="form-control" value="{{$bussiness_name}}" id="select_user_name" onkeyup="getUsers(this.value);"  autocomplete="off">      
                        <input type="hidden" name="store_id" id="store_id" value="{{$store_id}}">
                                        
                    </div>
                    <div class="respDrop" id="respDrop"></div>
                </div>                                
            </div>
        </div> 
        <div class="col-auto">
            <label for="">Sort By</label>
            <select name="sort" class="form-control" id="sort">
                <option value="days_high_to_low" @if($sort == 'days_high_to_low') selected @endif>Remaining Days - High to Low</option>
                <option value="days_low_to_high" @if($sort == 'days_low_to_high') selected @endif>Remaining Days - Low to High</option>
                <option value="amount_high_to_low" @if($sort == 'amount_high_to_low') selected @endif>Due Amount - High to Low</option>
                <option value="amount_low_to_high" @if($sort == 'amount_low_to_high') selected @endif>Due Amount - Low to High</option>
            </select>
        </div>
        <div class="col-auto">
            <label for="">Remaining Days</label>
            <select name="days_above" class="form-control" id="days_above">
                <option value="">All</option>
                <option value="45" @if($days_above == '45') selected @endif>45 Days & Above</option>
                <option value="60" @if($days_above == '60') selected @endif>60 Days & Above</option>
                <option value="90" @if($days_above == '90') selected @endif>90 Days & Above</option>
            </select>
        </div>
        <div class="col-auto">
            <label for="">Due Amount</label>
            <select name="amount_above" class="form-control" id="amount_above">
                <option value="">All</option>
                <option value="25000" @if($amount_above == '25000') selected @endif>Rs. 25000 Dr & Above</option>
                <option value="50000" @if($amount_above == '50000') selected @endif>Rs. 50000 Dr & Above</option>
                <option value="100000" @if($amount_above == '100000') selected @endif>Rs. 100000 Dr & Above</option>
                <option value="500000" @if($amount_above == '500000') selected @endif>Rs. 500000 Dr & Above</option>
                <option value="1000000" @if($amount_above == '1000000') selected @endif>Rs. 1000000 Dr & Above</option>
            </select>
        </div>        
    </div>
    </form> 
    <div class="row">
        <div class="col">
            <div class="row g-3 align-items-end"> 
                <div class="col">
                    
                </div>
                <div class="col-auto">
                    <span>Total {{$totalResult}} Records</span>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.report.store-due-csv') }}?sort={{$sort}}&days_above={{$days_above}}&amount_above={{$amount_above}}&store_id={{$store_id}}&bussiness_name={{$bussiness_name}}" class="btn btn-success select-md">CSV Export</a>
                </div>
            </div>
        </div>        
    </div> 
    <div class="row" id="myTable">
        <div class="col-sm-12">    
            <div class="table-responsive">
            <table class="table table-sm table-hover ledger">
                <thead>
                    <tr> 
                        <th>#</th>
                        <th>Store</th>
                        <th>Remaining Days</th>
                        <th>Due Amount</th>
                        <th>Action</th>
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
                    @forelse ($nthPageArr as $item)
                    
                    <tr class="store_details_row">                        
                        <td>{{$i}}</td>
                        <td>{{$item['store_name']}}</td>
                        <td>{{ $item['due_days'] }} days</td>
                        <td>Rs. {{ replaceMinusSign($item['amount']) }} {{ getCrDr($item['amount']) }}</td>
                        <td>
                            <a href="{{ route('admin.report.choose-ledger-user') }}?from_date={{$item['invoice_date']}}&to_date={{ date('Y-m-d') }}&user_type=store&store_id={{$item['store_id']}}&select_user_name={{$item['store_name']}}" class="btn btn-outline-success select-md">View Ledger</a>
                        </td>
                    </tr>                     
                    @php
                        $i++;
                    @endphp   
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">No record found</td>
                    </tr>    
                    @endforelse                                    
                </tbody>
            </table>
            {{-- <p>Custom Pagination</p> --}}
            @if (count($pagedArray)>1)
            <ul class="pagination">
                @if ($page > 1)
                <li class="page-item">
                    <a class="page-link" href="{{ route('admin.report.store-due-payment') }}?page={{($page-1)}}&sort={{$sort}}&days_above={{$days_above}}&amount_above={{$amount_above}}&store_id={{$store_id}}&bussiness_name={{$bussiness_name}}" rel="prev" aria-label="« Previous">‹</a>
                </li>
                @else
                <li class="page-item disabled" aria-disabled="true" aria-label="« Previous">
                    <span class="page-link" aria-hidden="true">‹</span>
                </li>
                @endif                
                @foreach ($pagedArray as $key => $pages)
                    @php
                        $pagekey = $key+1;
                    @endphp                    
                    <li class="page-item @if($pagekey == $page) active @endif">
                        @if ($pagekey == $page)
                            <span class="page-link">{{$pagekey}}</span>
                        @else
                            <a href="{{ route('admin.report.store-due-payment') }}?page={{$pagekey}}&sort={{$sort}}&days_above={{$days_above}}&amount_above={{$amount_above}}&store_id={{$store_id}}&bussiness_name={{$bussiness_name}}" class="page-link">{{$pagekey}}</a>
                        @endif                        
                    </li>                    
                @endforeach
                @if ($page < $nthPageNumber)
                <li class="page-item">
                    <a class="page-link" href="{{ route('admin.report.store-due-payment') }}?page={{($page+1)}}&sort={{$sort}}&days_above={{$days_above}}&amount_above={{$amount_above}}&store_id={{$store_id}}&bussiness_name={{$bussiness_name}}" rel="next" aria-label="Next »">›</a>
                </li>
                @else
                <li class="page-item disabled" aria-disabled="true" aria-label="Next »">
                    <span class="page-link" aria-hidden="true">›</span>
                </li>
                @endif                
            </ul> 
            @endif
            
            </div>
        </div>        
    </div>        
</section>
<script>
    $(document).ready(function(){
        
    })

    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        
        $('#select_user_name').val('');
        $('#store_id').val('');
        $('#ledgerForm').submit();
    });
    
    function getUsers(evt){
        var user_type = $('#user_type').val();
        if(evt.length > 0){            
            $.ajax({
                url: "{{ route('admin.ledger.getUsersByType') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: evt,
                    type: 'store'
                },
                success: function(result) {
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 user-dropdown " aria-labelledby="dropdownMenuButton">`;
                                                   
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
                        content += `<div class="dropdown-menu show w-100 user-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No user found</li></div>`;
                    }
                    $('.respDrop').html(content);
                }
            });
            
        }else{
            $('.respDrop').text('');            
        }
    }

    function fetchCode(id,name) {
        var user_type = $('#user_type').val();
        $('.user-dropdown').hide();        
        $('#store_id').val(id);            
        
        $('#select_user_name').val(name);        
        $('#ledgerForm').submit();
    }

    $('#days_above').on('change', function(){
        $('#ledgerForm').submit();
    })

    $('#amount_above').on('change', function(){
        $('#ledgerForm').submit();
    })
    $('#sort').on('change', function(){
        $('#ledgerForm').submit();
    })

</script>
@endsection
