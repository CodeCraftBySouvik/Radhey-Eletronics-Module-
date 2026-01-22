@extends('admin.layouts.app')
@section('page', 'Place Order')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Sales</li>
        <li><a href="{{ route('admin.threshold.list') }}">Price Requests</a> </li>
        <li>Place Order</li>
    </ul>
    <div class="row">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-body">                    
                    <div class="admin__content">
                        <aside>
                            <nav>Information</nav>
                        </aside>
                        <content>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="" class="col-form-label">Requested From</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->user->name }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="" class="col-form-label">Customer</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->store->bussiness_name }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="" class="col-form-label">Product</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->product->name }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Sell Price (Pcs per Ctn)</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ 'Rs. '.number_format((float)$data->sell_price, 2, '.', '') }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Requested Sell Price per Pcs</label>
                                </div>
                                @php
                                    $requested_sell_price_per_pcs = ($data->price / $data->pcs);
                                @endphp
                                <div class="col-9">
                                    <p class="m-0">{{ 'Rs. '.number_format((float)$requested_sell_price_per_pcs, 2, '.', '') }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="" class="col-form-label">PCS in Each CTNS</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->pcs }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Requested Price per Ctn</label>
                                </div>                                
                                <div class="col-9">
                                    <p class="m-0">{{ 'Rs. '.number_format((float)$data->price, 2, '.', '') }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="" class="col-form-label">No of Ctns</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->qty }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="" class="col-form-label">Requested At</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ date('d/m/Y H:i A', strtotime($data->created_at)) }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="" class="col-form-label">Request Status</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">Approved</p>
                                </div>
                            </div>
                        </content>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.threshold.save-requested-price-received-order') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{$id}}">
                        <h4>Place order</h4>
                        <input type="hidden" name="customer_approval" value="1">
                        
                        <div class="form-group mb-3">  
                            <textarea name="customer_approve_note" placeholder="Enter some note" rows="5" class="form-control"></textarea>
                            @error('customer_approve_note') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="form-group">
                            <a href="{{ route('admin.threshold.list') }}" class="btn btn-sm btn-danger">Back</a>
                            <button type="submit" class="btn btn-sm btn-success">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection