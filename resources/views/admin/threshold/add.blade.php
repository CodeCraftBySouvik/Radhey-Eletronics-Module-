@extends('admin.layouts.app')
@section('page', 'Approve Request')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Sales</li>
        <li><a href="{{ route('admin.threshold.list') }}">Price Requests</a> </li>
        <li>Approve Request</li>
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
                                    <label for="inputPassword6" class="col-form-label">Requested From</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->user->name }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Customer</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->store->bussiness_name }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Product</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->product->name }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Outstanding Price (Pcs per Ctn)</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ 'Rs. '.number_format((float)$data->sell_price, 2, '.', '') }} ({{$data->pcs}})</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">No of Ctns</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ $data->qty }}</p>
                                </div>
                            </div>
                            <div class="row mb-2 align-items-center">
                                <div class="col-3">
                                    <label for="inputPassword6" class="col-form-label">Requested Price</label>
                                </div>
                                <div class="col-9">
                                    <p class="m-0">{{ 'Rs. '.number_format((float)$data->price, 2, '.', '') }}</p>
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
                    <form method="POST" action="{{ route('admin.threshold.set-value') }}">
                    @csrf
                    <input type="hidden" name="id" value="{{$id}}">
                        
                        <h4>Approve Request </h4>
                        <div class="form-group mb-3">  
                            <input type="hidden" name="interval" value="7">                          
                            <select name="is_approved" class="form-control" id="">
                                <option hidden selected value="">Choose an option</option>
                                <option value="1">Approve</option>
                                <option value="2">Deny</option>
                            </select>
                            @error('is_approved') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <a href="{{ route('admin.threshold.list') }}" class="btn btn-sm btn-danger">Back</a>
                            <button type="submit" class="btn btn-sm btn-success">Ok</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
