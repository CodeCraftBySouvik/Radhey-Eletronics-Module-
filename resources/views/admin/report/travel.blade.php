@extends('admin.layouts.app')
@section('page', 'Travel Report')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Travel Report</li>
    </ul>
    <div class="row">
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
                    <form action="{{ route('admin.report.travel-report') }}" id="searchForm">
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
                            
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success select-md dates">Search</button>
                                <a href="{{ route('admin.report.travel-report') }}" class="btn btn-warning select-md dates">Reset</a>
                            </div>       
                        </div>
                    </form>
                </div>
            </div>
        </div> 
        <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Distance Covered</th>
                </tr>
            </thead>
            <tbody>
               
                @forelse ($data as $index => $item)
                @php
                    $sum_distance_text = $item->sum_total_distance / 1000 .' km';
                @endphp
                <tr>
                    <td>
                        {{ $item->name }}
                    </td>
                    <td>{{ $sum_distance_text }}</td>
                </tr>
                
                @empty
                <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                @endforelse
            </tbody>
        </table>   
        </div>
    </div>
</section>
@endsection