@extends('admin.layouts.app')
@section('page', 'GRN')
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
            </div>
            <div class="col-auto">
                
            </div>
            <div class="col-md-4">
                <form action="" id="searchForm">
                <div class="row">
                    <div class="col">
                        <input type="search" name="product_name" value="{{$product_name}}" placeholder="Search product by name ..." class="form-control select-md"  id="searchProText" onkeyup="getProductByName(this.value);" > 
                        <input type="hidden" name="product" id="searchProId" value="{{$product}}">
                        <div class="respDrop" id="respDrop"></div>
                    </div>
                    <input type="submit" hidden>
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
                    <p>
                        {{$totalData}} {{ ($totalData > 1) ? 'Total Items' : 'Total Item' }}
                    </p>
                </div>
            </div>
        </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Created At</th>
                            <th>GRN</th>
                            <th>PO</th>  
                            <th>Return</th>
                            <th>Products</th>                  
                            <th>Net Amount</th>
                            <th>Total Cartons</th>
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
                            $pro_id_arr = explode(",",$item->product_ids);
                            $groupConcatNames = groupConcatNames('products','name',$pro_id_arr);

                            $total_qty = 0;
                            foreach($item->stock_product as $sp){
                                $total_qty += $sp->quantity;
                            }
                        @endphp             
                        <tr>
                            <td> {{$i}} </td>   
                            <td> {{ date('d/m/Y', strtotime($item->created_at)) }}</td>                 
                            <td> {{$item->grn_no}} </td>
                            <td> 
                                @if (!empty($item->purchase_order_id))
                                <a href="{{ route('admin.purchaseorder.view',$item->purchase_order_id) }}" class="btn btn-outline-primary select-md">{{$item->purchase_order?$item->purchase_order->unique_id: ""}}</a>
                                @endif
                                
                            </td>
                            <td>
                                @if (!empty($item->return_id))
                                    <a href="{{ route('admin.returns.view',$item->return_id) }}" class="btn btn-outline-primary select-md">{{$item->returns?$item->returns->order_no: ""}}</a>
                                @endif
                                
                            </td>
                            <td>
                                {{-- {{$groupConcatNames}} --}}
                                <button type="button" class="btn btn-outline-success select-md" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}"> View Items ({{ count($item->stock_product) }})</button>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="prodTitle">
                                                    {{$item->grn_no}}
                                                </h5>
                                                
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="table-responsive">
                                                    <table class="table" id="prodHistTable">
                                                        <thead>
                                                            <th>#</th>
                                                            <th>Product</th>
                                                            <th>No of Cartons</th>
                                                            <th>Cost Price Per Piece</th>
                                                            <th>Total Price</th>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $j=1;
                                                            @endphp
                                                            @foreach ($item->stock_product as $products)
                                                            <tr>
                                                                <td>{{$j}}</td>
                                                                <td>{{$products->product?$products->product->name: ""}}</td>
                                                                <td>{{$products->quantity}}</td>
                                                                <td>Rs. {{ number_format((float)$products->piece_price, 2, '.', '') }}</td>
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
                            </td>
                            <td>XOF. {{ number_format((float)$item->total_price, 2, '.', '') }}</td>
                            <td>{{$total_qty}}</td>
                            <td>
                                <a href="{{ route('admin.grn.view',$item->id) }}" class="btn btn-outline-primary select-md">View</a>
                                @if (!empty($item->purchase_order_id))
                                <a href="{{ route('admin.grn.edit-amount',$item->id) }}" class="btn btn-outline-success select-md">Edit Amount</a>
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
            </div>
    </form>
    {{$data->links()}}
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
