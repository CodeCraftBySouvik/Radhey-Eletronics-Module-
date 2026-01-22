@extends('admin.layouts.app')
@section('page', 'Purchase Return')
@section('content')
<section>
    <div class="search__filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                @if (Session::has('message'))
                <div class="alert alert-success" role="alert">
                    {{ Session::get('message') }}
                </div>
                @endif
                @if (Session::has('errmessage'))
                <div class="alert alert-danger" role="alert">
                    {{ Session::get('errmessage') }}
                </div>
                @endif
            </div>
            <div class="col-md-6">
                <form action="" id="searchForm">
                <div class="row">
                    <div class="col-12 col-md-auto mb-2">
                        <a href="{{ route('admin.purchasereturn.add') }}" class="btn btn-outline-success select-md">Add New</a>
                       
                    </div>
                    <div class="col-12 col-md mb-2">
                        <input type="search" name="product_name" autocomplete="off" value="{{ $product_name }}" placeholder="Search product by name ..." class="form-control select-md"  id="searchProText" onkeyup="getProductByName(this.value);" > 
                        <input type="hidden" name="product_id" id="searchProId" value="{{ $product_id }}">
                        <div class="respDrop" id="respDrop"></div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <form>        
        <div class="filter">
            <div class="row align-items-center justify-content-between">                
                <div class="col"></div>
                <div class="col-auto">                    
                    <p>{{$total}} {{ ($total > 1) ? 'Total Items' : 'Total Item' }}</p>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>ID</th>
                        <th>Supplier</th>
                        <th>Products</th>
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
                    @forelse ($data as $index => $item)   
                                
                    <tr>
                        <td>
                            {{$i}}
                        </td>  
                        <td>{{date('d/m/Y', strtotime($item->created_at))}}</td>                  
                        <td>{{$item->order_no}}</td>
                        <td>
                            {{ !empty($item->supplier->name) ? $item->supplier->name : '' }}
                        </td>
                        <td>
                            <button type="button" class="btn btn-outline-success select-md" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}"> View Items ({{ count($item->purchase_return_products) }}) </button>
                            <!-- Modal -->
                            <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="prodTitle">
                                                {{$item->order_no}}
                                            </h5>
                                            
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <table class="table" id="prodHistTable">
                                                    <thead>
                                                        <th>#</th>
                                                        <th>Product</th>
                                                        <th>Quantity (Ctns)</th>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $j=1;
                                                        @endphp
                                                        @foreach ($item->purchase_return_products as $products)
                                                        <tr>
                                                            <td>{{$j}}</td>
                                                            <td>{{$products->product->name}}</td>
                                                            <td>{{$products->quantity}}</td>
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
                            @if (!empty($item->is_cancelled))
                                <span class="badge bg-danger">Cancelled</span>
                            @else
                                @if (empty($item->is_disbursed))
                                    <span class="badge bg-warning">Yet To Disburse</span>
                                @else
                                    <span class="badge bg-success">Disbursed</span>
                                @endif
                            @endif
                            
                        </td>
                        
                        <td>
                            @if (empty($item->is_cancelled))

                            <a href="{{ route('admin.purchasereturn.cancel', $item->id) }}" class="btn btn-outline-danger select-md" onclick="return confirm('Are you sure want to cancel ?');">Cancel</a>
                                
                                @if (empty($item->is_disbursed))
                                    <a href="{{ route('admin.purchasereturn.edit', $item->id) }}" class="btn btn-outline-primary select-md">Edit</a>
                                    
                                @else
                                    <a href="{{ route('admin.purchasereturn.pdf', $item->id) }}" class="btn btn-outline-success select-md">PDF</a>
                                    <a href="{{ route('admin.purchasereturn.details', $item->id) }}" class="btn btn-outline-primary select-md">Item Details</a> 
                                @endif
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
            {{$data->links()}}
        </div>        
    </form>
</section>

@endsection

@section('script')
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    })
    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        // alert('Cleared');
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
