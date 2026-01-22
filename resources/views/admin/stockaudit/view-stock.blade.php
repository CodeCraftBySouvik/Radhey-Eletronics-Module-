@extends('admin.layouts.app')
@section('page', 'Stock Audit Final - '.$entry_date)
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
               <a href="{{ route('admin.stockaudit.list') }}" class="btn btn-outline-danger">Back</a>
            </div>
            <div class="col-auto">
                <form action="" id="searchForm">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <select name="stock_status" class="form-control" id="stock_status">
                                <option value="">All</option>
                                <option value="matched" @if($stock_status == 'matched') selected @endif>Matched</option>
                                <option value="excess_system" @if($stock_status == 'excess_system') selected @endif>Excess In System</option>
                                <option value="excess_godown" @if($stock_status == 'excess_godown') selected @endif>Excess In Godown</option>
                            </select>
                        </div> 
                        <div class="col-auto">
                            <input type="search" id="search" name="search" class="form-control" placeholder="Search here.." value="{{ $search }}">
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
                <p>{{$countData}} Items</p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">           
            <table class="table">
                <thead>
                    <tr>                                
                        <th>#</th>
                        <th>Product</th>
                        <th>System Quantity (Ctns)</th>
                        <th>Godown Quantity (Ctns)</th>
                        <th>Stock Status</th>
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
                    <tr>                                  
                        <td>{{$i}}</td>
                        <td>
                            {{ $item->product->name }}
                        </td>
                        <td>
                            {{ $item->system_quantity }}
                        </td>
                        <td>
                            {{ $item->godown_quantity }}
                        </td>
                        <td>
                            @if ($item->stock_status == 'matched')
                                <span class="badge bg-success">Matched</span>
                            @elseif ($item->stock_status == 'excess_system')
                                <span class="badge bg-warning">Excess In System</span>
                            @elseif ($item->stock_status == 'excess_godown')
                            <span class="badge bg-danger">Excess In Godown</span>
                            @endif

                        </td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr><td colspan="100%" style="text-align: center;" class="small text-muted">No data found</td></tr>
                    @endforelse
                </tbody>
            </table>                 
        </div> 
        {{ $data->links() }}
        
    </div>
</section>
@endsection
@section('script')
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    });

    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });

    $('#stock_status').on('change', function(){
        $('#searchForm').submit();
    })
    
</script>
@endsection
