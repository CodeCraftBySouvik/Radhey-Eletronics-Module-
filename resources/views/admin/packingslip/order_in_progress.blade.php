@extends('admin.layouts.app')
@section('page', 'Packing Slips')
@section('content')
<style>
  /* Add space between SweetAlert buttons */
.swal2-actions {
    display: flex !important;
    justify-content: center; /* optional: center buttons */
    gap: 10px; /* this creates the space between buttons */
}

</style>
<section>
    <ul class="breadcrumb_menu">
        <li>Sales</li>
        <li>Packing Slip</li>
    </ul>   
    <div class="search__filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                @if (Session::has('message'))
                <div class="alert alert-success" role="alert">
                    {{ Session::get('message') }}
                </div>
                @endif
            </div>
            <div class="col-8">
                <div class="row">                        
                    
                    <div class="col">                        
                        <div class="row  align-items-center">
                            <form action="" id="searchForm">
                                <div class="row justify-content-end">
                                    <div class="col-6">
                                        <input type="search" name="search_product_name" placeholder="Search Product" class="form-control" value="{{$search_product_name}}" id="searchProText" onkeyup="getProductByName(this.value);" autocomplete="off">      
                                        <input type="hidden" name="search_product_id" id="searchProId" value="{{$search_product_id}}">
                                        <div class="respDrop" id="respDrop"></div>                    
                                    </div>  
        
                                    <div class="col-auto">
                                        <input type="search" name="search" id="search" class="form-control" placeholder="Search here.." value="{{$search}}" autocomplete="off">                                
                                    </div>
                                </div>
                            
                             
                            <input type="submit" hidden /> 
                            </form>                             
                        </div>                         
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <div class="filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">

            </div>
            <div class="col-auto">                        
                <p>{{$countData}} Total Packing Slips</p>                        
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">  
            @if($errors->any())                      
                {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
            @endif
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date & Time</th>
                            <th>Slip No</th>
                            <th>Order No</th>
                            <th>Store</th>
                            <th>Products</th>
                            <th>Total Items</th>
                            <th>Total Ctns</th>
                            <th>Total Pcs</th>
                            <th>Status</th>
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
                        @forelse ($data as $item)
                        @php
                            $count_qty = $count_pcs = 0;
                            $proids = array();
                            foreach($item->packingslip_products as $product){
                                $count_qty += $product->quantity;
                                $count_pcs += $product->pcs;
                                $proids[] = $product->product_id;
                                
                            }
                            $total_items = count($item->packingslip_products);
                            if(!empty($item->is_disbursed)){
                                $statusText = "Disbursed";
                                $statusClass = "success";
                            } else {
                                $statusText = "Yet to Disburse";
                                $statusClass = "warning";
                            }

                            $groupConcatNames = groupConcatNames('products','name',$proids);

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
                            <td>
                                {{$item->slipno}}
                            </td>
                            @if (isset($item->order) && is_object($item->order))
                                 <td>
                                   {{$item->order->order_no}}
                                </td>
                                @else
                                  <td>Order Not Found</td>
                            @endif
                           
                            <td>
                                <p class="small text-muted mb-1">
                                    
                                    @if (!empty($item->store->bussiness_name))
                                    <span> <strong>{{$item->store->bussiness_name}}</strong> </span>
                                    @else 
                                    <span> <strong>{{$item->store && $item->store->store_name}}</strong> </span> 
                                    @endif
                                
                                </p>
                            </td>
                            <td>
                                {{-- {{$groupConcatNames}} --}}
                                <button type="button" class="btn btn-outline-success select-md" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}"> View Items ({{ count($item->packingslip_products) }}) </button>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="prodTitle">
                                                    {{$item->slipno}} 
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
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $j=1;
                                                            @endphp
                                                            @foreach ($item->packingslip_products as $products)
                                                            <tr>
                                                                <td>{{$j}}</td>
                                                                <td>{{$products->product->name}}</td>
                                                                <td>{{$products->quantity}}</td>
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
                                {{$total_items}}
                            </td>
                            <td>
                                {{$count_qty}}
                            </td>
                            <td>
                                {{$count_pcs}}
                            </td>
                            <td>
                                <span class="badge bg-{{$statusClass}}">{{$statusText}}</span> <br/>

                                @if (empty($item->is_disbursed) && !empty($item->invoice_id))
                                    <span class="badge bg-warning">Invoice Is Edited, <br/> Please <br/> Stock Out <br/> To Complete <br/> Process</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.packingslip.get_pdf', $item->slipno) }}" class="btn btn-outline-success select-md">Download Packing Slip</a>
                                
                                @if (!empty($item->is_disbursed))
                                    @if (!empty($item->invoice_id) && !empty($item->invoice) && !empty($item->invoice->invoice_no))
                                        <a href="{{ route('admin.packingslip.view_invoice',$item->invoice->invoice_no) }}" class="btn  btn-outline-success select-md">Download Invoice</a>
                                    @else                                    
                                        <a href="javascript:void(0)" data-url="{{ route('admin.packingslip.raise_invoice_form', $item->id) }}" class="btn select-md btn-outline-warning deliverNowBtn">Deliver Now</a>                                    
                                    @endif   
                                @endif
                            </td>
                        </tr>
                        @php
                            $i++;
                        @endphp
                        @empty
                        <tr>
                            <td colspan="9" style="text-align: center;">
                                <span>No data found</span>
                            </td>
                        </tr>    
                        @endforelse
                    </tbody>
                </table>    
            </div>

            {{$data->links()}}           
        </div>
    </div>
    
</section>
@endsection

@section('script')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Handle Deliver Now button click
   $('.deliverNowBtn').on('click', function(e){
    e.preventDefault(); 
    var url = $(this).data('url'); 

    Swal.fire({
        title: 'Confirm Delivery!',
        html: `
            <div style="font-size:16px; font-weight:500; margin-bottom:10px;">
                ⚠️ You are about to <strong>deliver this order</strong>
            </div>
            <div style="font-size:14px; color:#d33; padding-left: 10px; margin-top:5px;">
                If you click <strong>Deliver Now</strong>, the invoice will be generated and reflected in the ledger.
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Deliver Now',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        focusConfirm: false,
        customClass: {
			popup: 'swal-wide',
            confirmButton: 'btn btn-success ml-2',  // uses bootstrap green button
            cancelButton: 'btn btn-secondary' // uses bootstrap grey button
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
});
</script>
<script>
	$(document).ready(function(){
		// Alert fade
		$('div.alert').delay(3000).slideUp(300);
	});
</script>

<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
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
