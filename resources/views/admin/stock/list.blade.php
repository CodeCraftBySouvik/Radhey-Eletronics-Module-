@extends('admin.layouts.app')
@section('page', 'Stock')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-8">  
            <table class="table">
                <thead>
                    <tr>                                
                        <th>#</th>
                        <th>Product</th>
                        <th>Box Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @forelse ($data as $index => $item)
                    <tr>                                  
                        <td>{{$i}}</td>
                        <td>
                            {{ $item->name }}
                            <div class="row__action">
                                <a href="{{ route('admin.stock.details', $item->id) }}">View Boxes</a>                                
                            </div>
                        </td>
                        <td>{{ $item->count_pro }}</td>
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

@endsection

