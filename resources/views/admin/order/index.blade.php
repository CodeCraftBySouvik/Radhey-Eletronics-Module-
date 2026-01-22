@extends('admin.layouts.app')
{{-- @section('page', 'Order') --}}
@if (!empty($status))
    @if ($status == 1)
        @section('page', 'Received Orders')
    @elseif ($status == 2)
        @section('page', 'Pending Orders')
    @elseif ($status == 3)
        @section('page', 'Cancelled Orders')
    @elseif ($status == 4)
        @section('page', 'Completed Orders')
    @endif
    @else
        @section('page', 'All Order')
@endif
@section('content')

@php
// $store_id = (isset($_GET['store_id']) && $_GET['store_id']!='')?$_GET['store_id']:'';
// $staff_id = (isset($_GET['staff_id']) && $_GET['staff_id']!='')?$_GET['staff_id']:'';
// $order_id = (isset($_GET['order_id']) && $_GET['order_id']!='')?$_GET['order_id']:'';
@endphp
<section>
    {{-- @if($errors->any())                      
        {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
    @endif --}}
    @if (Session::has('message'))
        <div class="alert alert-success" role="alert">
            {{ Session::get('message') }}
        </div>
    @endif
    <form action="" id="searchForm">
        <input type="hidden" name="status" value="{{$status}}">
        <div class="search__filter">
            <div class="row align-items-end justify-content-between">
                <div class="col">
                    
                </div>
                <div class="col-auto">
                    <input type="search" name="search" id="search" class="form-control select-md" placeholder="Search by order no ..." value="{{$search}}" autocomplete="off">                                
                </div>
                <div class="col-auto">
                    <input type="search" name="store_name" class="form-control select-md" id="store_name" placeholder="Search store by name" onkeyup="getStores(this.value);" style="width: 350px;" value="{{$store_name}}" autocomplete="off">
                    <input type="hidden" name="store_id" id="store_id" value="{{$store_id}}">
                    <div class="respDropStore" id="respDropStore" style="position: relative;"></div>
                                        
                </div>
                <div class="col-auto">
                    <select name="staff_id" class="form-control select-md" id="staff_id">
                        <option value="" hidden selected>Placed By</option>
                        @forelse ($users as $user)
                            <option value="{{$user->id}}" @if($staff_id == $user->id) selected @endif>{{$user->name}}</option>
                        @empty
                            
                        @endforelse
                    </select>
                </div>
                <div class="col-auto ms-auto">
                    <a href="{{ route('admin.order.index', ['status'=>$status]) }}" class="btn btn-outline-warning select-md">Reset</a>
                    <a href="{{ route('admin.order.add') }}" class="btn btn-outline-success select-md">Place New Order</a>
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
                    <p>Total {{$totalData}} Items</p>            
                </div>
            </div>
        </div>  
    </form> 
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
            
                <th>#</th>
                <th>Date & Time</th>
                <th>Order Id</th>
                <th>Store Details</th>
                <th>Placed By</th>
                <th>Products</th>
                <th>Order Amount (Inc.Tax)</th>
                <th>Created From</th>
                <th>Order Status</th>
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
                @foreach ($data as $index => $item)
                @php
                    $proids = array();
                    foreach($item->order_products as $pro){
                        $proids[] = $pro->product_id;
                    }

                    // dd($item->packingslip);
                    
                    
                    $groupConcatNames = groupConcatNames('products','name',$proids);
                    
                    
                    $viewDetailText = "Generate Packing Slip";
                    if($item->status == 3 || $item->status == 4){
                        $viewDetailText = "View Details";
                    }
                @endphp
                <tr>
                    <td>
                        {{$i}}
                    </td>               
                    <td>
                        <p class="small">
                            Created At:- {{date('j M Y g:i A', strtotime($item->created_at))}}
                        </p>
                        @if ($item->created_at != $item->updated_at)
                        <p class="small">
                            Updated At:- {{date('j M Y g:i A', strtotime($item->updated_at))}}
                        </p>
                        @endif
                    </td>   
                    <td>
                        <p class="small text-dark mb-1">#{{$item->order_no}}</p>
                        
                    </td>
                    <td>
                        <p class="small text-muted mb-1">
                            @if (!empty($item->stores['bussiness_name']))
                            <span>Name: <strong>{{$item->stores['bussiness_name']}}</strong> </span>  
                            @else 
                            <span>Name: <strong>{{$item->stores?$item->stores['store_name']:""}}</strong> </span>  
                            @endif
                            <br>  
                            @if (!empty($item->stores['contact']))
                            <span>Mobile : <strong>{{$item->stores['contact']}}</strong> </span> <br>   
                            @endif
                            @if (!empty($item->stores['whatsapp']))
                            <span>WhatsApp : <strong>{{$item->stores['whatsapp']}}</strong> </span>   
                            @endif
                        </p>
                    </td>
                    <td>
                        <p class="small text-muted mb-1">{{$item->users?$item->users['name']: ""}}</p>
                    </td>
                    <td>
                        {{-- <p>{{$groupConcatNames}}</p> --}}
                        <button type="button" class="btn btn-outline-success select-md" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}"> View Items ({{ count($item->orderProducts) }}) </button>
                        <!-- Modal -->
                        <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="prodTitle">
                                            {{$item->order_no}} 
                                            @if (!empty($item->stores['bussiness_name']))
                                            / 
                                            {{$item->stores['bussiness_name']}}
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
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $j=1;
                                                    @endphp
                                                    @foreach ($item->order_products as $products)
                                                    <tr>
                                                        <td>{{$j}}</td>
                                                        <td>{{$products->productDetails?$products->productDetails->name:""}}</td>
                                                        <td>{{$products->qty}}</td>
                                                        <td>{{$products->pcs}}</td>
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
                    </td>
                    <td>
                        <p class="small text-muted mb-1">Rs {{ number_format((float) $item->final_amount, 2, '.', '')}}</p>
                    </td>     
                    <td>
                        <span class="badge bg-success">{{ ucwords($item->created_from) }}</span>
                    </td>      
                    <td>
                        <?php 
                            if ($item->status == 1) {
                                $status = "Received";
                                $status_class = "success";
                            }else if ($item->status == 2) {
                                $status = "Pending";
                                $status_class = "warning";
                            }else if ($item->status == 3) {
                                $status = "Cancelled";
                                $status_class = "danger";
                            }else  {
                                $status = "Completed";
                                $status_class = "primary";
                            }
                        ?>
                        <span class="badge bg-{{$status_class}}">
                            {{$status}}
                        </span>
                    </td>    
                    <td>
                        
                        @if($item->status == 1)
                            {{-- {{ count($item->pending_thresholds) }} --}}
                            @if (count($item->pending_thresholds) > 0)
                                <span class="badge bg-info">
                                    Items Under Threshold Approval
                                </span>
                            @else
                                @if (in_array($item->status,[1,2]))
                                    @if (empty($item->packingslip))
                                        <a href="{{ route('admin.packingslip.add',$item->id) }}" class="btn btn-outline-primary select-md">Generate Packing Slip</a>
                                    @else
                                        <a href="{{ route('admin.packingslip.edit',$item->packingslip->id) }}" class="btn btn-outline-primary select-md">Edit Packing Slip</a>
                                    @endif
                                                                
                                @endif
                                {{-- @if (count($item->thresholds) == 0) --}}
                                    <a href="{{ route('admin.order.edit',$item->id) }}" class="btn btn-outline-success select-md">Edit</a>
                                {{-- @endif --}}
                                
                                <a href="{{ route('admin.order.status', [$item->id, 3]) }}" onclick="return confirm('Are you sure want to cancel the order?');" class="btn btn-outline-danger select-md" >Cancel Order</a>
                            @endif
                            
                        @endif
                        <a href="{{ route('admin.order.view', $item->id) }}" class="btn btn-outline-success select-md">Details</a>
                    </td>            
                </tr>
                @php
                    $i++;
                @endphp
                @endforeach
            </tbody>
        </table>
        {{$data->links()}}
    </div>
</section>
@endsection
@section('script')
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    });
    $('#paginate').change(function(){
        $('#searchForm').submit();
    })

    $('#store_name').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#store_name').val('');
        $('#store_id').val(0);
        $('#searchForm').submit();
    });
    $('#search').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });

    $('#staff_id').on('change', function(){
        $('#searchForm').submit();
    });

    // $('.searchDropdown').on('change', function(){
    //     $('#searchForm').submit();
    // });

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
