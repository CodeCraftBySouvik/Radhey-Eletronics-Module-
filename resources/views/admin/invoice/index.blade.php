@extends('admin.layouts.app')
@section('page', 'Invoices')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-12">
            <div class="search__filter">
                <form action="" id="searchForm">  
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        @if (Session::has('message'))
                        <div class="alert alert-success" role="alert">
                            {{ Session::get('message') }}
                        </div>
                        @endif   
                    </div>                  
                    <div class="col-auto">
                        <select name="type" id="type" class="form-control select-md">
                            <option value="" @if(empty($type)) selected @endif>All Type</option>
                            <option value="gst" @if($type == 'gst') selected @endif>GST</option>
                            <option value="non_gst" @if($type == 'non_gst') selected @endif>NON-GST</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="search" name="store_name" class="form-control select-md" id="store_name" placeholder="Search store by name" onkeyup="getStores(this.value);" value="{{$store_name}}" autocomplete="off">
                        <input type="hidden" name="store_id" id="store_id" value="{{$store_id}}">
                        <div class="respDropStore" id="respDropStore" style="position: relative;"></div>
                    </div>
                    <div class="col-auto">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <input type="search" id="term" name="term" class="form-control select-md" placeholder="Search invoice or slip" autocomplete="off" value="{{$term}}">
                            </div>                                
                            <input type="submit" hidden />
                        </div>
                    </div>
                </div>
            </form>
            </div>
            
            <div class="filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">

                    </div>
                    <div class="col-auto">                        
                        <p>{{$total}} Total invoices</p>                        
                    </div>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>      
                        <th>#</th>    
                        <th>Date & Time</th>              
                        <th>Invoice No</th>    
                        <th>Slip No</th>     
                        <th>Order No</th>               
                        <th>Store</th>
                        <th>Products</th>
                        <th>Amount</th>
                        {{-- <th>Due Amount</th> --}}
                        {{-- <th>Payment Status</th> --}}
                        <th>Action</th>
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
                    @forelse ($data as $index => $item)

                    @php
                        $payment_status = 'Not Paid';
                        $payment_class = 'danger';
                        if($item->payment_status == 0){
                            $payment_status = 'Not Paid';
                            $payment_class = 'danger';
                        }else if($item->payment_status == 1){
                            $payment_status = 'Half Paid';
                            $payment_class = 'warning';
                        }else if($item->payment_status == 2){
                            $payment_status = 'Full Paid';
                            $payment_class = 'success';
                        }
                    @endphp
                    <tr>        
                        <td>{{$i}}</td>   
                        <td>
                            <p class="m-0">
                                Created At:- {{date('d/m/Y H:i A', strtotime($item->created_at))}}
                            </p>
                            @if (!empty($item->updated_by))
                            <p class="m-0">
                                Updated At:- {{date('d/m/Y H:i A', strtotime($item->updated_at))}}
                            </p>
                            @endif
                            
                        </td>             
                        <td>{{$item->invoice_no}} </td>
                        <td>
                            <a href="{{ route('admin.packingslip.get_pdf',$item->packingslip->slipno) }}" class="btn btn-outline-secondary select-md">{{$item->packingslip->slipno}}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.order.view', $item->order_id) }}" class="btn btn-outline-secondary select-md">{{$item->order->order_no}}</a> 
                        </td>
                        <td>
                            <p class="small text-muted mb-1">
                                
                                @if (!empty($item->store->bussiness_name))
                                <span><strong>{{$item->store->bussiness_name}}</strong> </span> 
                                @else
                                <span><strong>{{$item->store->store_name}}</strong> </span>
                                @endif
                                                              
                            </p>
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-success select-md" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}"> View Items ({{count($item->products)}}) </button>
                            <!-- Modal -->
                            <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="prodTitle">
                                                {{$item->invoice_no}} 
                                                @if (!empty($item->store->bussiness_name))
                                                / 
                                                {{$item->store->bussiness_name}}
                                                @endif
                                                
                                            </h5>
                                            
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <table class="table" id="prodHistTable">
                                                    <thead>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>Total Ctns</th>
                                                        <th>Total Pcs</th>
                                                        <th>Rate</th>
                                                        <th>Amount</th>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $j=1;
                                                        @endphp
                                                        @foreach ($item->products as $products)
                                                        <tr>
                                                            <td>{{$j}}</td>
                                                            <td>{{$products->product_name}}</td>
                                                            <td>{{$products->quantity}}</td>
                                                            <td>{{$products->pcs}}</td>
                                                            <td>Rs. {{ number_format((float)$products->single_product_price, 2, '.', '') }}</td>
                                                            <td>Rs. {{ number_format((float)$products->total_price, 2, '.', '') }}</td>
                                                        </tr>
                                                        @php
                                                            $j++;
                                                        @endphp
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ok</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <a href="{{ route('admin.invoice.barcode',$item->id) }}" class="btn btn-outline-success select-md">View Barcodes</a>
                        </td>
                        <td>Rs. {{ number_format((float)$item->net_price, 2, '.', '') }}</td>
                        {{-- <td>Rs. {{ number_format((float)$item->required_payment_amount, 2, '.', '') }}</td> --}}
                        {{-- <td>
                            <span class="badge bg-{{$payment_class}}">{{$payment_status}}</span>
                        </td> --}}
                        <td>
                            <a href="{{ route('admin.invoice.edit',$item->id) }}" class="btn btn-outline-success select-md">Edit</a>
                            <a href="{{ route('admin.packingslip.view_invoice', $item->invoice_no) }}" class="btn select-md btn-outline-success">Download</a>   
                            {{-- <a href="{{ route('admin.invoice.payments', $item->id ) }}" class="btn select-md btn-outline-success">View Payments</a> --}}
                            <a href="{{ route('admin.invoice.revoke',$item->id) }}" class="btn select-md btn-outline-warning" onclick="return confirm('Are you sure want to revoke?');">Revoke</a>
                        </td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                    @endforelse
                </tbody>
            </table>      
        </div>        
            {{$data->links()}}
        </div>       
    </div>
</section>
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    })
    
    $('#term').on('search', function () { 
        // $('#term').val('');       
        $('#searchForm').submit();
    });
    $('#store_name').on('search', function () {   
        // $('#store_name').val('');
        $('#store_id').val('');
        $('#searchForm').submit();
    });

    $('#type').on('change', function(){
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
        $('#searchForm').submit();
        
    }

    
</script>
@endsection

@section('script')

@endsection