@extends('admin.layouts.app')
@section('page', 'Product')
@section('content')
<section>
    <form action="{{ route('admin.product.index') }}" id="searchForm">
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
                <a href="{{ route('admin.product.create') }}" class="btn btn-outline-success select-md">Add New Product</a>
            </div>
            <div class="col-4">    
                <input type="search" name="term" id="term" class="form-control select-md" placeholder="Search here.." value="{{$term}}" autocomplete="off">                                
                                          
            </div>
        </div>
    </div>
    <input type="submit" hidden />  
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
                <p>Total {{$total}} Items</p>            
            </div>
        </div>
    </div>  
    </form>                          
    <form action="{{ route('admin.product.bulkSuspend') }}" method="post">
    @csrf
    <div class="filter">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <input id="btnSuspend" type="submit" class="btn btn-outline-danger select-md" value="Suspend">
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="check-column">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll">
                            <label class="form-check-label" for=""></label>
                        </div>
                    </th>
                    {{-- <th class="text-center"><i class="fi fi-br-picture"></i></th> --}}
                    <th>#</th>
                    <th>Published Date</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Sell Price (Last PO)</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="product_body">
                @php
                    if(empty(Request::get('page')) || Request::get('page') == 1){
                        $i=1;
                    } else {
                        $i = (((Request::get('page')-1)*$paginate)+1);
                    } 
                @endphp
                @forelse ($data as $index => $item)
                @php
                    
                    $checkStockPO = checkStockPO($item->id,0);
                    $stock = $checkStockPO['stock'];
                    $pieces = $checkStockPO['pieces'];
                @endphp
                <tr>
                    <td class="check-column">
                        @if ($item->status != 0)
                        <div class="form-check">
                            <input name="suspend_check[]" class="data-check" type="checkbox"  value="{{$item->id}}">
                        </div>
                        @endif
                    </td>
                    {{-- <td class="text-center column-thumb">
                        @if(!empty($item->image))
                        <img src="{{ asset($item->image) }}">
                        @endif
                    </td> --}}
                    <td>
                        {{$i}}
                    </td>
                    <td>{{date('d/m/Y', strtotime($item->created_at))}}</td>
                    <td>
                        {{$item->name}}
                        <div class="row__action">
                            <a href="{{ route('admin.product.edit', $item->id) }}">Edit</a>
                            <a href="{{ route('admin.product.view', $item->id) }}">View</a>
                            <a href="{{ route('admin.product.status', $item->id) }}">{{($item->status == 1) ? 'Suspend' : 'Active'}}</a>
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('admin.category.view', $item->category->id) }}">{{$item->category ? $item->category->name : ''}}</a>
                        >
                        {{$item->subCategory ? $item->subCategory->name : 'NA'}}
                    </td>    
                    <td>
                        {{ 'Rs. '.number_format((float)$item->sell_price, 2, '.', '') }}
                    </td>
                    <td>
                        {{$stock}} ctns ({{$pieces}} pcs) 
                    </td>            
                    <td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Suspended'}}</span></td>
                </tr>

                @php
                    $i++;
                @endphp
                @empty
                <tr>
                    <td colspan="100%" class="small text-muted text-center">No data found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{$data->links()}}
    </div>    
    </form>
       
</section>
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
        $('#btnSuspend').prop('disabled', true);        
        $("#checkAll").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
            var checkAllStatus = $("#checkAll:checked").length;
            // console.log(checkAllStatus)
            if(checkAllStatus == 1){
                $('#btnSuspend').prop('disabled', false);
            }else{
                $('#btnSuspend').prop('disabled', true);
            }
        });
        
        $('.data-check').change(function () {
            $('#btnSuspend').prop('disabled', false);
            var total_checkbox = $('input:checkbox.data-check').length;
            var total_checked = $('input:checkbox.data-check:checked').length;
            
            if(total_checked == 0){
                $('#btnSuspend').prop('disabled', true);
            }
          
            if(total_checkbox == total_checked){
                console.log('All checked')
                $('#checkAll').prop('checked', true);
            }else{
                console.log('Not All checked')
                $('#checkAll').prop('checked', false);
            }
        })

        
    });

    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });

    $('#paginate').change(function(){
        $('#searchForm').submit();
    })

    // $('#term').on('keyup', function(){
    //     var timer;
    //     clearTimeout(timer);
    //     timer=setTimeout(()=>{ 
    //         $('#searchForm').submit();
    //      },1500);
    // });    
</script>
@endsection
