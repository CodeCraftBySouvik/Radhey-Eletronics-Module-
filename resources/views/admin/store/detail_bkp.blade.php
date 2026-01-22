@extends('admin.layouts.app')
@section('page', 'Store Details')
@section('content')
<section>
    <div class="row">
        <ul class="breadcrumb_menu">
            <li>Master</li>
            <li><a href="{{ route('admin.store.index') }}">Store Management</a></li>
            <li>Store Details</li>
        </ul>
        <div class="col-sm-12">
            <div class="card equal_height">
                <div class="card-body">                    
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>Store Name:</strong></p>
                        </div>
                        <div class="col-sm-9 text-right">
                            <p>{{ $data->store_name }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>Email:</strong></p>
                        </div>
                        <div class="col-sm-9 text-right">
                            <p>{{ $data->email }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>Contact Details:</strong></p>
                        </div>
                        <div class="col-sm-9 text-right">
                            <p>{{ $data->contact }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>WhatsApp:</strong></p>
                        </div>
                        <div class="col-sm-9 text-right">
                            <p>{{ $data->whatsapp }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>Address:</strong></p>
                        </div>
                        <div class="col-sm-9 text-right">
                            <p>{{ $data->address }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>City:</strong></p>
                        </div>
                        <div class="col-sm-9 text-right">
                            <p>{{ $data->city }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>State:</strong></p>
                        </div>
                        <div class="col-sm-9 text-right">
                            <p>{{ $data->state }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3">
                            <p><strong>Pin:</strong></p>
                        </div>
                        <div class="col-sm-9 text-right">
                            <p>{{ $data->pin }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <a href="{{ route('admin.store.index') }}" class="btn btn-sm btn-danger">Back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</section>
@endsection

{{-- google location section --}}
@section('script')

@endsection
