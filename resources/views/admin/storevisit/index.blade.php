@extends('admin.layouts.app')
@section('page', 'Store Visit  > '.$user_name)
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li>Staff Management</li>
        <li><a href="{{ route('admin.staff.index') }}">Staff</a></li>
        <li>Store Visit</li>
    </ul>    
    <div class="row">
        <div class="col-sm-12">
            <div class="search__filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">                
                        <div class="row g-3 align-items-center">                            
                            <div class="col-auto">
                                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-danger btn-sm">Back</a>
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
                    <p> Total Items</p>
                </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>  
                            <th>#</th>    
                            <th>Store</th>
                            <th>Start Date & Time</th>
                            <th>Start Location</th>
                            <th>End Date & Time</th>
                            <th>End Location</th>
                            <th>Total Distance Covered</th>
                            <th>Visit Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i=1;
                        @endphp
                        @forelse ($data as $index => $item)
                        <tr>
                            <td>{{$i}}</td>
                            <td>{{$item->store_name}}</td>
                            <td>
                                @if (!empty($item->start_date) && !empty($item->start_time))
                                {{date('d/m/Y', strtotime($item->start_date))}} - {{date('H:i A', strtotime($item->start_time))}} 
                                @endif                            
                            </td>
                            <td>
                                {{-- @if (strlen($item->start_location) > 50)
                                   {{ substr($item->start_location,0,50)."..." }} 
                                @else --}}
                                    {{ $item->start_location }}
                                {{-- @endif --}}
                            </td>
                            <td>
                                @if (!empty($item->end_date) && !empty($item->end_time))
                                    {{date('d/m/Y', strtotime($item->end_date))}} - {{date('H:i A', strtotime($item->end_time))}} 
                                @endif
                            </td>
                            <td>
                                {{-- @if (strlen($item->end_location) > 50)
                                   {{ substr($item->end_location,0,50)."..." }} 
                                @else --}}
                                    {{ $item->end_location }}
                                {{-- @endif --}}
                            </td>
                            <td> {{$item->total_distance_text}}</td>    
                            <td class="text-center column-thumb">
                                
                                @if($item->visit_image)
                                    <img src="{{asset($item->visit_image)}}" alt="" style="height: 100px" class="mr-4">
                                @else
                                    <img src="{{asset('admin/images/placeholder-image.jpg')}}" alt="" class="mr-4" style="width: 100px;height: 100px;border-radius: 50%;">
                                @endif
                            </td>  
                            <td>
                                <a href="{{ route('admin.visit.mapdirection', $item->id) }}" class="btn btn-sm btn-success">Map View</a>
                                {{-- <a href="{{ route('admin.visit.details', $item->id) }}" class="btn btn-sm btn-success">Location Updates</a> --}}
                            </td>                  
                        </tr>
                        @php
                            $i++;
                        @endphp
                        @empty
                        <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                        @endforelse
                    </tbody>
                </table>                      
                {{$data->links()}}
            </div>
        </div>
    </div>
</section>
<script>   

    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });

    $('#term').on('keyup', function(){
        var timer;
        clearTimeout(timer);
        timer=setTimeout(()=>{ 
            $('#searchForm').submit();
        },1500);
    });
    
</script>
@endsection
