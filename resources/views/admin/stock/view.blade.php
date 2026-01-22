@extends('admin.layouts.app')
@section('page', 'Product Stock Details' )
@section('content')
<section>    
    
    <div class="container" id="print_div">
        <div>
        {{-- <h5>Order No:- <span>{{$unique_id}}</span></h5> --}}
        <div class="row">
            @forelse ($data as $index => $item)
            <div class="col-md-3">
                <span>Order No:- <strong>{{ $item->po_unique_id }}</strong></span>
                <p><span>GRN:- <strong>{{ $item->grn_no }}</strong></span></p>
                <img alt="Barcoded value {{$item->barcode_no}}" src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{$item->barcode_no}}&includetext">
                
            </div>
            @empty
            
            @endforelse
        </div>
        </div>
    </div>
   
</section>
@endsection
