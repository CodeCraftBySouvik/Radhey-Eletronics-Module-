@extends('admin.layouts.app')
@section('page', 'View Boxes' )
@section('content')
<section>    
    
    <div class="container" id="print_div">
        <div>
        {{-- <h5>Order No:- <span>{{$unique_id}}</span></h5> --}}
        <div class="row">
             @foreach ($data as $arr)
            <div class="col-md-3">
                <img alt="Barcoded value {{$arr->barcode_no}}"
     src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{$arr->barcode_no}}&includetext">
            </div>
            @endforeach
        </div>
        </div>
    </div>
   
</section>
@endsection
@section('script')

@endsection