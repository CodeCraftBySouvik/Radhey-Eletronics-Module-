@extends('admin.layouts.app')
@section('page', 'Discounts')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Accounting</li>
        <li>Discounts</li>
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
                        <a class="btn select-md btn-outline-success" href="{{ route('admin.discount.add') }}">Add New</a>
                    </div>
                    <div class="col-auto">
                        <form action="" id="searchForm">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <input type="search" id="search" name="search" class="form-control select-md" placeholder="Search here.." value="{{$search}}" autocomplete="off">
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
                        <p>{{$total}} Items</p>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table table-sm table-hover ledger">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Voucher No</th>
                        <th>Customer</th>
                        <th>Narration</th>
                        <th>Discount Amount</th>
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
                        <td>{{$i}}</td>
                        <td>{{ date('d/m/Y', strtotime($item->entry_date)) }}</td>
                        <td>{{ $item->voucher_no }}</td>
                        <td>
                            <p class="small text-muted mb-1">
                                @if (!empty($item->store->store_name))
                                <span>Person Name: {{$item->store->store_name}}</span> <br/>
                                @endif
                                @if (!empty($item->store->bussiness_name))
                                <span>Company Name: {{$item->store->bussiness_name}}</span> <br/>
                                @endif
                            </p>
                        </td>
                        <td>
                            {{$item->narration}}
                        </td>
                        <td>Rs. {{ number_format((float)$item->amount, 2, '.', '') }}</td>
                        <td>
                            <a href="{{ route('admin.discount.edit',$item->id) }}" class="btn btn-outline-success select-md">Edit</a>
                        </td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr><td colspan="100%" >No data found</td></tr>
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
        $('div.alert').delay(3000).slideUp(300);
    })
    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });
</script>
@endsection