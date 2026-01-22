@extends('admin.layouts.app')
@section('page', 'Show Cartons')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li><a href="{{ route('admin.purchaseorder.index') }}?type=po">PO</a></li>
        <li>Show Cartons</li>
    </ul>   
    <div class="search__filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                @if (Session::has('message'))
                <div class="alert alert-success" role="alert">
                    {{ Session::get('message') }}
                </div>
                @endif
            </div>
            <div class="col-12 col-md-auto">
                <div class="row">                        
                    <div class="col-auto">
                        <a href="{{ route('admin.barcodes',$id) }}" class="btn  btn-outline-info select-md">Download All Barcodes</a>
                    </div>
                    <div class="col">                        
                        <div class="row g-3 align-items-center">
                            <form action="" id="searchForm">
                                
                            <div class="col-auto">
                                <input type="search" name="search" id="search" class="form-control select-md" placeholder="Search barcode.." value="{{$search}}" autocomplete="off">                                
                            </div>  
                            <input type="submit" hidden /> 
                            </form>                             
                        </div>                         
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="filter">
        <div class="row align-items-center justify-content-between">
            <div class="col-auto">
                <a href="{{ route('admin.purchaseorder.index') }}" class="btn btn-outline-danger select-md">Back</a>
            </div>
            <div class="col-auto">    
                {{$totalData}} items  
            </div>
        </div>
    </div>
    <div class="row">
        @forelse ($data as $product_id => $barcodes) 
        @php
            $pro_name = getSingleAttributeTable('products',$product_id,'name');
        @endphp
        <div class="col-lg-12">
            <h5>
                {{$pro_name}}
            </h5>
            <div class="row">
                @forelse ($barcodes as $box)
                <div class="col-sm-3">
                    <span title="{{$box->barcode_no}}">
                        {!! $box->code_html !!}
                        <span style="width: 100%; display: block;  color: #000000;">{{$box->barcode_no}}</span>
                    </span>   
                </div>
                @empty
                @endforelse
            </div>
        </div>
        @empty
        @endforelse
    </div>
</section>

@endsection
@section('script')
<script>
    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });

    $('#search').on('keyup', function(){
        var timer;
        clearTimeout(timer);
        timer=setTimeout(()=>{ 
            $('#searchForm').submit();
         },1500);
    });
</script>
@endsection
