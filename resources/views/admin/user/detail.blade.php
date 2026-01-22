@extends('admin.layouts.app')
@section('page', 'Supplier Details')
@section('content')
<section>    
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li><a href="{{ route('admin.user.index', 'supplier') }}">Supplier Management</a></li>
        <li>Supplier Details</li>
    </ul>
    <div class="row">
        <div class="col-sm-9">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            {{-- {{ dd($data[0]) }} --}}
                            @if($data[0]->gst_file)
                            <img src="{{asset($data[0]->gst_file)}}" alt="" style="height: 200px" class="mr-4">
                            @else
                            <img src="{{asset('admin/images/placeholder-image.jpg')}}" alt="" class="mr-4" style="width: 100px;height: 100px;border-radius: 50%;">
                            @endif
                        </div>
                        <div class="col-md-10">
                            <h3>{{ $data[0]->name }}</h3>
                            <p><span class="text-muted">Email :</span> {{ $data[0]->email }}</p>
                            <p><span class="text-muted">Contact Details:</span> {{ $data[0]->mobile }}</p>
                            <p><span class="text-muted">WhatsApp Details:</span> {{ $data[0]->whatsapp_no }}</p>
                            <p><span class="text-muted">GST Number :</span> {{ $data[0]->gst_number }}</p>
                            {{-- <p><span class="text-muted">Credit Limit :</span> {{ $data[0]->credit_limit }}</p>
                            <p><span class="text-muted">Credit Days :</span> {{ $data[0]->credit_days }}</p> --}}
                            <p>Published<br/>{{date('d M Y', strtotime($data[0]->created_at))}}</p>
                            <div class="d-flex">
                                <div class="col-6">
                                    <p class="small">Billing Address</p>
                                    <p><span class="text-muted">Address : </span> {{ $data[0]->billing_address }}</p>
                                    <p><span class="text-muted">Landmark :</span> {{ $data[0]->billing_landmark }}</p>
                                    <p><span class="text-muted">City :</span> {{ $data[0]->billing_city }}</p>
                                    <p><span class="text-muted">State :</span> {{ $data[0]->billing_state }}</p>
                                    <p><span class="text-muted">Pincode :</span> {{ $data[0]->billing_pin }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="small">Shipping Address</p>
                                    <p><span class="text-muted">Address : </span> {{ $data[0]->shipping_address }}</p>
                                    <p><span class="text-muted">Landmark :</span> {{ $data[0]->shipping_landmark }}</p>
                                    <p><span class="text-muted">City :</span> {{ $data[0]->shipping_city }}</p>
                                    <p><span class="text-muted">State :</span> {{ $data[0]->shipping_state }}</p>
                                    <p><span class="text-muted">Pincode :</span> {{ $data[0]->shipping_pin }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card shadow-sm">
                <div class="card-header">
                    Action
                </div>
                {{-- @if(Auth::user()->type==1)
                <div class="card-body text-end">
                    <a href="{{ route('admin.user.index', 'customer') }}" class="btn btn-sm btn-danger">Back to Customer </a>
                </div>
                @else --}}
                <div class="card-body text-end">
                    <a href="{{ route('admin.user.index', 'supplier') }}" class="btn btn-sm btn-danger">Back to Supplier </a>
                </div>
                {{-- @endif --}}
            </div>
        </div>
    </div>
</section>
@endsection
