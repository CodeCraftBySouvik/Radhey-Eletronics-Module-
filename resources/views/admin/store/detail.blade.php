@extends('admin.layouts.app')
@section('page', 'Customer Details')
@section('content')
<section>    
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li><a href="{{ route('admin.store.index') }}">Customer Management</a> </li>
        <li>Edit Details</li>
    </ul>
    <div class="row">
        <div class="col-sm-9">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            {{-- {{ dd($data[0]) }} --}}
                            @if(!empty($data->gst_file))
                            <img src="{{asset($data->gst_file)}}" alt="" style="height: 200px" class="mr-4">
                            @else
                            <img src="{{asset('admin/images/placeholder-image.jpg')}}" alt="" class="mr-4" style="width: 100px;height: 100px;border-radius: 50%;">
                            @endif

                            
                        </div>
                        <div class="col-md-10">
                            <h3>{{ $data->store_name }}</h3>
                            @if(!empty($data->bussiness_name))
                            <p><span class="text-muted">Business Name :</span> {{ $data->bussiness_name }}</p>
                            @endif
                            @if(!empty($data->email))
                            <p><span class="text-muted">Email :</span> {{ $data->email }}</p>
                            @endif
                            @if(!empty($data->contact))
                            <p><span class="text-muted">Contact Details:</span> {{ $data->contact }}</p>
                            @endif
                            @if(!empty($data->whatsapp))
                            <p><span class="text-muted">WhatsApp Details:</span> {{ $data->whatsapp }}</p>
                            @endif
                            @if(!empty($data->gst_number))
                            <p><span class="text-muted">GST Number :</span> {{ $data->gst_number }}</p>
                            @endif
                            @if(!empty($data->credit_limit))
                            <p><span class="text-muted">Credit Limit :</span> {{ $data->credit_limit }}</p>
                            @endif
                            @if(!empty($data->credit_days))
                            <p><span class="text-muted">Credit Days :</span> {{ $data->credit_days }}</p>
                            @endif
                            <p>Published<br/>{{date('d/m/Y', strtotime($data->created_at))}}</p>
                            <p><span class="text-muted">Address Outstation:</span> {{ !empty($data->address_outstation) ? 'Yes' : 'No' }}</p>
                            <div class="d-flex">
                                <div class="col-6">
                                    <p class="small">Billing Address</p>
                                    <p><span class="text-muted">Address : </span> {{ $data->billing_address }}</p>
                                    <p><span class="text-muted">Landmark :</span> {{ $data->billing_landmark }}</p>
                                    <p><span class="text-muted">City :</span> {{ $data->billing_city }}</p>
                                    <p><span class="text-muted">State :</span> {{ $data->billing_state }}</p>
                                    <p><span class="text-muted">Pincode :</span> {{ $data->billing_pin }}</p>
                                </div>
                                <div class="col-6">
                                    <p class="small">Shipping Address</p>
                                    <p><span class="text-muted">Address : </span> {{ $data->shipping_address }}</p>
                                    <p><span class="text-muted">Landmark :</span> {{ $data->shipping_landmark }}</p>
                                    <p><span class="text-muted">City :</span> {{ $data->shipping_city }}</p>
                                    <p><span class="text-muted">State :</span> {{ $data->shipping_state }}</p>
                                    <p><span class="text-muted">Pincode :</span> {{ $data->shipping_pin }}</p>
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
                <div class="card-body text-end">
                    <a href="{{ route('admin.store.index') }}" class="btn btn-sm btn-danger">Back to Customer </a>
                </div>                
            </div>
        </div>
    </div>
</section>
@endsection
