@extends('admin.layouts.app')
@section('page', 'Locations')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li>Staff Management</li>
        <li><a href="{{ route('admin.staff.index') }}">Staff</a></li>
        <li>Store Visit</li>
        <li>Locations</li>
    </ul>
    <div class="row">
        <div class="col-sm-12">
            <div class="search__filter">
                <div class="row align-items-center justify-content-between">
                    <div class="row">
                        <div class="col">                
                            <div class="row g-3 align-items-center">                            
                                <div class="col-auto">
                                    <a href="{{ route('admin.visit.index', $data[0]->user_id) }}" class="btn btn-outline-danger btn-sm">Back</a>
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
                    <p> Total Items</p>
                </div>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>  
                        <th>#</th>    
                        <th>Location Point</th>
                        <th>Distance</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                        
                    @endphp
                    @forelse ($data as $index => $item)

                    @php
                        $name = "";
                        if($i == 1){
                            $name = "   (Starting Point)";
                        }else if($i == (count($data)) ){
                            $name = "   (Ending Point)";
                        }
                    @endphp
                    <tr>
                        <td><span>{{$i}}</span></td>
                        <td>
                            <span>
                                {{ $item->latitude }} - {{ $item->longitude }} 
                            <strong>{{$name}}</strong></td>
                            </span>                            
                        <td><span>{{ $item->distance }}</span></td>                 
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                    @endforelse
                </tbody>
            </table>            
        </div>
    </div>
</section>
<script>   

    
</script>
@endsection
