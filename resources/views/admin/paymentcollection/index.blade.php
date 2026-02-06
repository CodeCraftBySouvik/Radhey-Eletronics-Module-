@extends('admin.layouts.app')
@section('page', 'Payment Collection')
@section('content')

@php
$store_id = (isset($_GET['store_id']) && $_GET['store_id']!='')?$_GET['store_id']:'';
$staff_id = (isset($_GET['staff_id']) && $_GET['staff_id']!='')?$_GET['staff_id']:'';
@endphp
<section>
    <ul class="breadcrumb_menu">
        <li>Accounting</li>
        <li><a href="{{ route('admin.paymentcollection.index') }}">Payment Collection</a></li>
    </ul>
    <div class="row">
        <div class="col-sm-12">
            <div class="search__filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        @if (Session::has('message'))
                        <div class="alert alert-success" role="alert">
                            {{ Session::get('message') }}
                        </div>
                        @endif
                    </div>
                    <div class="col-auto">
                        <form action="" method="GET">
                            <div class="row g-3 align-items-center">                    
                                
                                <div class="col-auto">
                                    <input type="text" name="store_name" class="form-control select-md" id="store_name" placeholder="Search store by name" onkeyup="getStores(this.value);" value="{{ $store_name }}" style="width: 350px;">
                                    <input type="hidden" name="store_id" id="store_id" value="{{ $store_id }}">
                                    <div class="respDropStore" id="respDropStore" style="position: relative;"></div>
                                    @error('store_id') <p class="small text-danger">{{ $message }}</p> @enderror                 
                                </div>
                                <div class="col-auto">                    
                                    <select name="staff_id" class="form-control select-md">
                                        <option value="" hidden selected>Collected By</option>
                                        @foreach($users as $user)
                                        <option value="{{$user->id}}" @if($staff_id==$user->id){{"selected"}}@endif>{{$user->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                <button type="submit" class="btn btn-outline-success  select-md">Search</button>
                                </div>
                                <div class="col-auto">
                                <a href="{{ route('admin.paymentcollection.index') }}" class="btn btn-outline-danger  select-md">Clear</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        
                    </div>
                    <div class="col-auto">
                        <p>{{$total}} Items</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table table-sm table-hover ledger">
                <thead>
                <tr> 
                    <th>#</th>
                    <th>Payment Date</th>
                    <th>Collected By</th> 
                    <th>Customer</th>
                    <th>Collection Amount</th>
                    <th>Collected From</th>
                    <th>Approval</th>
                    <th>Action</th>
                    <th>Entered at</th>    
                </tr>
                </thead>
                <tbody>
                    @php
                        if(empty(Request::get('page')) || Request::get('page') == 1){
                            $i=1;
                        } else {
                            $i = (((Request::get('page')-1)*$paginate)+1);
                        } 
                    @endphp
                    @foreach ($data as $index => $item)
                    <tr class="store_details_row">   
                        <td>{{$i}}</td>        
                             <td>
                            <p class="small text-muted mb-1"> 
                                {{date('d/m/Y', strtotime($item->cheque_date))}}
                            </p>                            
                        </td>  
                        <td>
                            @if (!empty($item->users))
                                <p class="small text-muted mb-1">{{$item->users['name']}}</p>
                            @endif                            
                        </td>         
                        <td>                            
                            <p class="small text-muted mb-1">
                                @if (!empty($item->stores['bussiness_name']))
                                <span><strong>{{$item->stores['bussiness_name']}}</strong> </span> 
                                @endif
                            </p>                            
                        </td>
                        <td>
                            <p class="small text-muted mb-1">Rs. {{number_format((float)$item->collection_amount, 2, '.', '')}} ({{ucwords($item->payment_type)}})</p>
                        </td>

                        <td>
                            <span class="badge bg-success">{{ucwords($item->created_from)}}</span>
                        </td>  
                        <td>
                            @if (!empty($item->is_ledger_added))
                                <span class="badge bg-success">Approved</span>                                
                            @else
                                <span class="badge bg-danger">Not Approved</span>  
                                
                            @endif
                        </td>
                        <td>
                            @if (empty($item->is_ledger_added))
                                <a href="{{ route('admin.accounting.add_payment_receipt',$item->id) }}" class="btn btn-md btn-warning select-md">Approve</a>
                                
                                <a href="{{ route('admin.paymentcollection.remove',$item->id) }}" onclick="return confirm('Are you sure want to remove?');" class="btn btn-outline-danger select-md">Remove</a>
                            @endif
                            
                            @if (!empty($item->is_ledger_added))
                                <a href="{{ route('admin.paymentcollection.revoke',$item->id) }}" onclick="return confirm('Are you sure want to revoke payment?');" class="btn btn-outline-warning select-md">Revoke</a>
                            @endif
                            
                        </td>   
                         <td>
                            <p class="small text-muted mb-1">{{ date('d/m/Y H:i A', strtotime($item->created_at)) }} </p>
                        </td>  
                    </tr>  
                    <tr>                        
                        <td colspan="5" class="store_details_column">
                            <div class="store_details">
                                <table class="table">
                                    <tr>
                                        <td>
                                            <span>Customer Name: <strong>{{$item->stores['store_name']}} </strong> </span> 
                                        </td>
                                        @if (!empty($item->stores['bussiness_name']))
                                        <td>
                                            <span>Company Name: <strong>{{$item->stores['bussiness_name']}} </strong> </span> 
                                        </td> 
                                        @endif  
                                        @if (!empty($item->stores['contact']))
                                        <td>                                            
                                            <span>Phone: <strong>{{$item->stores['contact']}} </strong> </span>  
                                        </td>  
                                        @endif    
                                        @if (!empty($item->stores['whatsapp']))                                      
                                        <td>
                                            <span>WhatsApp: <strong>{{$item->stores['whatsapp']}} </strong> </span> 
                                        </td>  
                                        @endif
                                    </tr>                                    
                                    <tr>   
                                        @if (!empty($item->bank_name))
                                        <td><span>Bank: <strong>{{ ($item->bank_name)}}</strong></span></td>    
                                        @endif
                                        @if (!empty($item->payment_type))
                                        <td><span>Bank: <strong>{{ ucwords($item->payment_type)}}</strong></span></td>    
                                        @endif
                                        @if (!empty($item->chq_utr_no))
                                        <td><span>Cheque / UTR No: <strong>{{ ucwords($item->cheque_number)}}</strong></span></td>    
                                        @endif
                                        @if (!empty($item->cheque_date))
                                        <td><span>Payment Date: <strong>{{ date('d/m/Y', strtotime($item->cheque_date))}}</strong></span></td>    
                                        @endif
                                        @if (!empty($item->vouchar_no))
                                        <td><span>Voucher No: <strong>{{ ($item->vouchar_no)}}</strong></span></td>    
                                        @endif
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @php
                        $i++;
                    @endphp          
                    @endforeach
                </tbody>
            </table>
        </div>
            {{-- {{$data->links()}} --}}
            {{$data->links('pagination::bootstrap-4')}}

        </div>
    </div>
</section>
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    })

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
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton">`;

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
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No store found</li></div>`;
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
        
        
    }
</script>
@endsection
