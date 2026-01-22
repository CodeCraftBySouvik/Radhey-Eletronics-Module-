@extends('admin.layouts.app')
@section('page', strtoupper($type))
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
            <div class="col-md-6">
                <form action="" id="searchForm">
                <input type="hidden" name="type" value="{{$type}}">
                <div class="row">
                    <div class="col-12 col-md-auto mb-2">
                        @if ($type == 'po')
                            <a href="{{ route('admin.purchaseorder.create') }}" class="btn btn-outline-success select-md">Add New PO</a>
                        @endif
                        
                    </div>
                    <div class="col-12 col-md mb-2">
                        <input type="search" name="product_name" value="{{$product_name}}" placeholder="Search product by name ..." class="form-control select-md"  id="searchProText" onkeyup="getProductByName(this.value);" > 
                        <input type="hidden" name="product" id="searchProId" value="{{$product}}">
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
                        <th>Ordered At</th>
                        <th>ID</th>
                        <th>Products</th>
                        <th>Net Amount</th>
                        <th>Total Ctns</th>
                        <th>Total Pcs</th>
                        {{-- <th>Scanned Ctns</th> --}}
                        <th>Archived Ctns</th>
                        <th>Supplier</th>
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
                    @php
                        $pro_id_arr = explode(",",$item->product_ids);
                        // dd($pro_id_arr);
                        $groupConcatNames = groupConcatNames('products','name',$pro_id_arr);
                    @endphp             
                    <tr>
                        <td>
                            {{$i}}
                        </td>  
                        <td>{{date('d/m/Y', strtotime($item->created_at))}}</td>                  
                        <td>{{$item->unique_id}}</td>
                        <td>
                            {{-- {{$groupConcatNames}} --}}
                            <button type="button" class="btn btn-outline-success select-md" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}"> View Items ({{ count($item->purchase_order_products) }}) </button>
                            <!-- Modal -->
                            <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="prodTitle">
                                                {{$item->unique_id}}
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
                                                        <th>Pieces per Ctn</th>
                                                        @if (Auth::user()->designation == null )
                                                        <th>Cost Price Per Piece</th>
                                                        @endif
                                                        <th>Total Price</th>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $j=1;
                                                        @endphp
                                                        @foreach ($item->purchase_order_products as $products)
                                                        <tr>
                                                            <td>{{$j}}</td>
                                                            <td>{{$products->product}}</td>
                                                            <td>{{$products->qty}}</td>
                                                            <td>{{$products->pcs}}</td>
                                                            @if (Auth::user()->designation == null)
                                                            <td>Rs. {{ number_format((float)$products->piece_price, 2, '.', '') }}</td>
                                                            @endif
                                                            
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
                        <td>
                            XOF. {{ number_format((float)$item->total_price, 2, '.', '') }}
                        </td>
                        @php
                            $total_qty = $total_pcs = 0;
                            foreach($item->purchase_order_products as $pro){
                                $total_qty += $pro->qty;
                                $total_pcs += ($pro->qty*$pro->pcs);
                            }
                           
                        @endphp
    
                        <td>
                            {{ $total_qty }}
                        </td>
                        <td>{{ $total_pcs }}</td>
                        {{-- <td>
                            {{ $item->scanned_boxes }}
                        </td> --}}
                        <td>
                            {{ count($item->count_archived) }}
                        </td>
                        <td>
                            {{ !empty($item->supplier->name) ? $item->supplier->name : '' }}
                        </td>
                        <td>
                            @if ($item->status == 1)
                                <span class="badge bg-warning">Pending</span>
                            @elseif ($item->status == 2)
                                <span class="badge bg-success">Received</span>
                            @elseif ($item->status == 3)
                                <span class="badge bg-secondary">Ongoing</span>
                            @endif
                        </td>
                        <td>
                            @if ($item->status == 1)
                                @if (empty($item->scanned_boxes))
                                    <a href="{{ route('admin.purchaseorder.edit', $item->id) }}" class="btn btn-outline-primary select-md">Edit</a>
                                @endif
                                <a href="{{ url('admin/purchaseorder/showboxes'.'/'.$item->id) }}" class="btn btn-outline-primary select-md">Cartons</a>
                                <a href="{{ route('admin.purchaseorder.grn', $item->id) }}" class="btn btn-outline-primary select-md">Generate GRN</a>
                            @else
                                
                            @endif
                            <a href="{{ route('admin.purchaseorder.view', $item->id) }}" class="btn btn-outline-primary select-md">Details</a>
                            <a href="{{ route('admin.purchaseorder.pdf', $item->id) }}" class="btn btn-outline-success select-md">PDF</a>
                            @if (count($item->count_archived) > 0)
                                <a href="{{ route('admin.purchaseorder.archived', $item->id) }}" class="btn btn-outline-success select-md">Archived</a>
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
