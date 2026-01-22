@extends('admin.layouts.app')
@section('page', 'Store Notes')
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
                    </div>                    
                    
                    <div class="col-auto">
                        <form action="{{ route('admin.report.store-notes') }}" id="searchForm">
                            <div class="row g-3 align-items-end">
                                <div class="col-6 col-sm-auto">
                                    <div class="form-group">
                                        <label for="">From</label>
                                        <input type="date" name="from_date"  id="from_date" class="form-control select-md dates" value="{{ $from_date }}"  max="{{ $to_date }}" placeholder="From" autocomplete="off">
                                    </div>
                                </div>
                                <div class="col-6 col-sm-auto">
                                    <div class="form-group">
                                        <label for="">To</label>
                                        <input type="date" name="to_date"  id="to_date" class="form-control select-md dates" value="{{ $to_date }}" placeholder="To" max="{{ date('Y-m-d') }}" min="{{ $from_date }}" autocomplete="off">  
                                    </div>
                                </div>
                                <div class="col-12 col-sm-auto">
                                    <div class="form-group">
                                        <label for="">Store</label>
                                        <select name="store_id" class="form-control select-md dates" id="store_id">
                                            <option value=""> - Select Store - </option>
                                            @forelse ($store as $item)
                                                <option value="{{$item->id}}" @if($store_id == $item->id) selected @endif>{{$item->store_name}}</option>
                                            @empty
                                                
                                            @endforelse
                                        </select>
                                    </div>                                    
                                </div>
                                <div class="col-6 col-sm-auto">
                                    <div class="form-group">
                                        <label for="">Staff</label>
                                        <select name="user_id" class="form-control select-md dates" id="user_id">
                                            <option value=""> - Select Staff - </option>
                                            @forelse ($staff as $item)
                                                <option value="{{$item->id}}" @if($user_id == $item->id) selected @endif>{{$item->name}}</option>
                                            @empty
                                                
                                            @endforelse
                                        </select>
                                    </div>
                                </div>  
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-success select-md dates">Search</button>
                                    <a href="{{ route('admin.report.store-notes') }}" class="btn btn-warning select-md dates">Reset</a>
                                </div>       
                            </div>
                        </form>
                    </div>
                </div>
            </div>              
            <div class="filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">                                                        
                        <div class="col-auto">
                            {{-- <button id="btnSuspend" type="submit" class="btn btn-outline-danger btn-sm">Suspend</button> --}}
                        </div>                            
                    </div>
                    <div class="col-auto">                            
                        {{-- <p>{{$total}} Items</p> --}}
                    </div>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="5%">Created At</th>
                        <th width="20%">Store</th>
                        <th width="20%">Staff</th>
                        <th width="50%">Notes</th>
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
                        <td>{{ date('d/m/Y h:i A', strtotime($item->created_at)) }}</td>
                        <td>{{ $item->store->store_name }}</td>
                        <td>{{ $item->user->name }}</td>
                        <td>{{ $item->details }}</td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr><td colspan="5" style="text-align: center">No data found</td></tr>
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
                
    });

    $('input[type=search]').on('search', function () {        
        $('#searchForm').submit();
    });

    $('#from_date')

    
</script>
@endsection
@section('script')

<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    })

    
</script>
@endsection