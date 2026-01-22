@extends('admin.layouts.app')
@section('page', 'Product Details')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li>Product Management</li>
        <li><a href="{{ route('admin.product.index') }}">Product</a> </li>
        <li>Details</li>
    </ul>
    <form method="post" action="{{ route('admin.product.update', $data[0]->id) }}" enctype="multipart/form-data">@csrf
        <div class="row">
            <div class="col-sm-3">
                <div class="card shadow-sm">
                    <div class="card-header">Main image</div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">
                            <label for="thumbnail"><img id="output" src="{{ asset($data[0]->image) }}"/></label>
                        </div>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header">More images</div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">
                        @foreach($images as $index => $singleImage)
                            <label for="thumbnail"><img id="output" src="{{ asset($singleImage->image) }}" class="img-thumbnail mb-3"/></label>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <h2>{{$data[0]->name}}</h2>
                        </div>
                        <div class="form-group mb-3">
                            <p><span class="text-muted">Category : </span>{{$data[0]->category->name}} | <span class="text-muted">Sub-category : </span>{{$data[0]->subCategory->name}}</p>
                        </div> 
                        
                        {{-- <hr>
                        <div class="form-group mb-3">
                            <h4>
                                <span class="text-muted small">Rs {{$data[0]->cost_price}}</span> 
                                <span class="text-danger">Rs {{$data[0]->sell_price}}</span> 
                            </h4>
                        </div> --}}
                        <hr>
                        <div class="form-group mb-3">
                            <p class="small">Short Description</p>
                            {!! $data[0]->short_desc !!}
                        </div>
                        <hr>
                        <div class="form-group mb-3">
                            <p class="small">Description</p>
                            {!! $data[0]->desc !!}
                        </div>
                        <hr>
                        <div class="form-group mb-3">
                            <p class="small">Cost Price (As per Latest PO)</p>
                            <span class="text-danger">Rs {{ number_format((float)$data[0]->cost_price, 2, '.', '')}}</span>
                        </div>
                        <hr>
                        <div class="form-group mb-3">
                            <p class="small">Sell Price (As per Latest PO)</p>
                            <span class="text-danger">Rs {{ number_format((float)$data[0]->sell_price, 2, '.', '') }}</span>
                        </div>
                        <hr>
                        <div class="form-group mb-3">
                            <p class="small">Pieces per Carton</p>
                            <span class="text-danger">{{ $data[0]->pcs }}</span>
                        </div>
                        <hr>
                        <div class="form-group mb-3">
                            <p class="small">HSN Code (As per Latest PO)</p>
                            {!! $data[0]->hsn_code !!}
                        </div>
                        <hr>
                        {{-- <div class="form-group mb-3">
                            <p class="small">Pcs per Ctns (As per Latest PO)</p>
                            {!! $data[0]->unit_value !!}
                            
                        </div>
                        <hr>
                        <div class="form-group mb-3">
                            <p class="small">Weight per Ctns (As per Latest PO)</p>
                            {!! $data[0]->weight_value !!} {!! $data[0]->weight_type !!}
                            
                        </div>
                        <hr>                         --}}
                        <div class="admin__content">
                            <aside>
                                <nav>GST (In Percent)</nav>
                            </aside>
                            <content>                                
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputprice6" class="col-form-label">IGST (%)</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small">{{$data[0]->igst}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputprice6" class="col-form-label">CGST (%)</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small">{{$data[0]->cgst}}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputprice6" class="col-form-label">SGST (%)</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="small"> {{$data[0]->sgst}}</p>
                                    </div>
                                </div>
                            </content>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('script')
    <script>
        
    </script>
@endsection