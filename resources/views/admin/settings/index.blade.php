@extends('admin.layouts.app')

@section('page', 'Settings')

@section('content')
<section>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    <div class="search__filter">
                        <div class="row align-items-center justify-content-between">
                            <div class="col">
                                <ul>
                                    <li class="active"><a href="{{ route('admin.settings.index') }}">All <span class="count">({{$data->count()}})</span></a></li>
                                    @php
                                        $activeCount = $inactiveCount = 0;
                                        foreach ($data as $setKey => $setVal) {
                                            if ($setVal->status == 1) $activeCount++;
                                            else $inactiveCount++;
                                        }
                                    @endphp
                                    <li><a href="{{ route('admin.settings.index', ['status' => 'active'])}}">Active <span class="count">({{$activeCount}})</span></a></li>
                                    <li><a href="{{ route('admin.settings.index', ['status' => 'inactive'])}}">Inactive <span class="count">({{$inactiveCount}})</span></a></li>
                                </ul>
                            </div>
                            <div class="col-auto">
                                <form action="{{ route('admin.settings.index') }}" method="GET">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-auto">
                                            <input type="search" name="term" class="form-control" placeholder="Search here.." id="term" value="{{app('request')->input('term')}}" autocomplete="off">
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Search Settings</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.bulkDestroy') }}">
                    <div class="filter">
                        <div class="row align-items-center justify-content-between">
                        <div class="col">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <select name="bulk_action" class="form-control">
                                        <option value=" hidden selected">Bulk Action</option>
                                        <option value="delete">Delete</option>
                                    </select>
                                </div>
                                <div class="col-auto">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Apply</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            @php
                            if (!empty($_GET['status'])) {
                                if ($_GET['status'] == 'active') {
                                    ($activeCount>1) ? $itemShow = 'Items' : $itemShow = 'Item';
                                    echo '<p>'.$activeCount.' '.$itemShow.'</p>';
                                } elseif ($_GET['status'] == 'inactive') {
                                    ($inactiveCount>1) ? $itemShow = 'Items' : $itemShow = 'Item';
                                    echo '<p>'.$inactiveCount.' '.$itemShow.'</p>';
                                }
                            } else {
                                ($data->count() > 1) ? $itemShow = 'Items' : $itemShow = 'Item';
                                echo '<p>'.$data->count().' '.$itemShow.'</p>';
                            }
                            @endphp
                            {{-- <p>{{$data->count()}} Items</p> --}}
                        </div>
                        </div>
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th class="check-column">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="flexCheckDefault" onclick="headerCheckFunc()">
                                        <label class="form-check-label" for="flexCheckDefault"></label>
                                    </div>
                                </th>
                                <th>Page</th>
                                <th>Content</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $index => $item)
                            @php
                            if (!empty($_GET['status'])) {
                                if ($_GET['status'] == 'active') {
                                    if ($item->status == 0) continue;
                                } else {
                                    if ($item->status == 1) continue;
                                }
                            }
                            @endphp

                            <tr>
                                <td class="check-column">
                                    <input name="delete_check[]" class="tap-to-delete" type="checkbox" onclick="clickToRemove()" value="{{$item->id}}" 
                                    @php
                                    if (old('delete_check')) {
                                        if (in_array($item->id, old('delete_check'))) {
                                            echo 'checked';
                                        }
                                    }
                                    @endphp>
                                </td>
                                <td>
                                    {{strtoupper($item->page_heading)}}
                                </td>
                                <td>
                                {{$item->content}}
                                <div class="row__action">
                                    <a href="{{ route('admin.settings.view', $item->id) }}">Edit</a>
                                    <a href="{{ route('admin.settings.view', $item->id) }}">View</a>
                                </div>
                                </td>
                                <td>Published<br/>{{date('d M Y', strtotime($item->created_at))}}</td>
                                <td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Inactive'}}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </form>    
                </div>
            </div>
        </div>
    </div>
</section>
@endsection