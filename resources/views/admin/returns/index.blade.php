@extends('admin.layouts.app')
@section('page', 'Returns')
@section('content')
<section>
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
                        @if (Session::has('errMsg'))
                        <div class="alert alert-danger" role="alert">
                            {{ Session::get('errMsg') }}
                        </div>
                        @endif
                    </div>                    
                    <div class="col-auto">
                        <a class="btn btn-outline-success select-md" href="{{ route('admin.returns.add') }}">Add New</a>
                    </div>
                    <div class="col-auto">
                        <form action="" id="searchForm">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <input type="search" id="search" name="search" class="form-control select-md" placeholder="Search here.." value="{{$search}}"  autocomplete="off">
                                </div>                                
                                <input type="submit" hidden />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">                                                        
                        <div class="col-auto">
                            
                        </div>                            
                    </div>
                    <div class="col-auto">                            
                        <p>{{$totalData}} Items</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover ledger">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Requested At</th>
                            <th>Order No</th>
                            <th>Store</th>
                            <th>Products</th>
                            <th>Amount</th>
                            <th>Goods In</th>
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
                            $proidArr = array();
                            foreach($item->return_products as $products){
                                $proidArr[] = $products->product_id;
                            }
                            $groupConcatNames = groupConcatNames('products','name',$proidArr);
                        @endphp
                        <tr>       
                            <td>{{$i}}</td>                 
                            <td>{{ date('d/m/Y', strtotime($item->created_at)) }}</td>
                            <td>{{ $item->order_no }}</td>
                            <td>
                                <p class="small text-muted mb-1">
                                    
                                    @if (!empty($item->store->bussiness_name))
                                    <span> {{$item->store->bussiness_name}}</span> 
                                    @else
                                    <span> {{$item->store->store_name}}</span>
                                    @endif
                                </p>
                            </td>
                            <td>
                                {{-- {{$groupConcatNames}} --}}
                                <button type="button" class="btn btn-outline-success select-md" data-bs-toggle="modal" data-bs-target="#exampleModal{{$item->id}}"> View Items ({{ count($item->return_products) }})</button>
                                <!-- Modal -->
                                <div class="modal fade" id="exampleModal{{$item->id}}" tabindex="-1" aria-labelledby="" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="prodTitle">
                                                    {{$item->order_no}} 
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
                                                            @foreach ($item->return_products as $products)
                                                            <tr>
                                                                <td>{{$j}}</td>
                                                                <td>{{$products->product->name}}</td>
                                                                <td>{{$products->quantity}}</td>
                                                                <td>{{$products->pcs * $products->quantity}}</td>
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
                            <td>Rs. {{ number_format((float)$item->amount, 2, '.', '') }}</td>
                            <td>
                                @if (!empty($item->is_cancelled))
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    
                                    @if (!empty($item->is_goods_in))
                                        <span class="badge bg-success">Yes</span> 
                                        <span class="badge bg-success">{{ucwords($item->goods_in_type)}}</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.returns.view',$item->id) }}" class="btn btn-outline-success select-md">View</a>
                                <a href="{{ route('admin.returns.download-cash-slip',$item->order_no) }}" class="btn btn-outline-success select-md">Download</a>
                                <a href="{{ route('admin.returns.barcode',$item->id) }}" class="btn btn-outline-success select-md">Boxes</a>

                                @if (empty($item->is_cancelled))
                                  
                                    <a href="{{ route('admin.returns.cancel', $item->id) }}" onclick="return confirm('Are you sure want to cancel?');" class="btn btn-outline-danger select-md">Cancel</a>  
                                @endif

                                @if (!empty($item->is_cancelled))
                                    
                                @else
                                
                                    @if (empty($item->is_goods_in))
                                        <a href="{{ route('admin.returns.edit',$item->id) }}" class="btn btn-outline-success select-md">Edit</a>
                                        <a href="{{ route('admin.returns.goods-in',$item->id) }}" class="btn btn-outline-success select-md">Goods In</a>
                                    @endif

                                    @if (!empty($item->is_goods_in))
                                        <a href="{{ route('admin.returns.edit-amount',$item->id) }}" class="btn btn-outline-success select-md">Edit Amount</a>
                                    @endif    
                                @endif
                                
                            </td>
                        </tr>
                        @php
                            $i++;
                        @endphp
                        @empty
                        <tr><td colspan="8" style="text-align:center;">No data found</td></tr>
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
<script>
    $(document).ready(function(){
        $('div.alert').delay(10000).slideUp(300);
    });
    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });
</script>
@endsection