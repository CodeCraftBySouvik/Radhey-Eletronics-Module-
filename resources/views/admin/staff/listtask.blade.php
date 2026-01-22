@extends('admin.layouts.app')
@section('page', 'List Task  > '.$name)
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li>Staff Management</li>
        <li><a href="{{ route('admin.staff.index') }}">Staff</a></li>        
        <li>List Task</li>
    </ul>
    <div class="row">
        <div class="col-sm-12">
            <div class="search__filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        
                    </div>
                    <div class="col-auto">
                        <a class="btn btn-sm btn-outline-success" href="{{ route('admin.staff.createtask', $id) }}">Add New</a>
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('admin.staff.index') }}">Back</a>
                    </div>
                    <div class="col-auto">
                        
                    </div>
                </div>
            </div>
            @if (\Session::has('message'))
            <div class="alert alert-success">
                <ul>
                    <li>{!! \Session::get('message') !!}</li>
                </ul>
            </div>
            @endif
            <div class="filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">                            
                                                
                    </div>
                    <div class="col-auto">
                        <p>{{$total}} Total Items</p>
                    </div>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Stores</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @forelse ($data as $index => $item)

                    @php
                        $today = date('Y-m-d');
                        $start_date = $item->start_date;
                        $end_date = $item->end_date;
                        $is_editable = 0;
                        $tr_class = "";
                        if (($today >= $start_date) && ($today <= $end_date)){
                            $is_editable = 1;
                            $tr_class = "table-info";
                        }

                        
                        if(!empty($is_editable)){
                            $btnText = "Edit";
                        }else{
                            $btnText = "View";
                        }
                    @endphp

                    <tr class="{{$tr_class}}">
                        <td>{{$i}}</td>
                        <td>
                        {{ date('d/m/Y', strtotime($item->start_date)) }} - {{ date('d/m/Y', strtotime($item->end_date)) }}
                        <div class="row__action">
                            <a href="{{ route('admin.staff.edittask', [$id,$item->id]) }}">{{$btnText}}</a>                            
                        </div>
                        </td>
                        <td>
                            {{$item->store_names}}
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
</section>

@endsection
