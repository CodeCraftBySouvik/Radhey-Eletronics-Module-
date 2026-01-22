@extends('admin.layouts.app')
@section('page', 'Staff Details')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li>Staff Management</li>
        <li><a href="{{ route('admin.staff.index') }}">Staff</a></li>
        <li>Staff Details</li>
    </ul>
    <div class="row">
        <div class="col-sm-9">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            {{-- {{ dd($data[0]) }} --}}
                            @if($data->image)
                                <img src="{{asset($data->image)}}" alt="" style="height: 100px" class="mr-4">
                            @else
                                <img src="{{asset('admin/images/placeholder-image.jpg')}}" alt="" class="mr-4" style="width: 100px;height: 100px;border-radius: 50%;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h3>{{ $data->name}}</h3>
                            <p><span class="text-muted">Designation :</span> {{ ($data->designation_name) }}</p>
                            @if (!empty($data->email))                            
                            <p><span class="text-muted">Email :</span> {{ $data->email }}</p>    
                            @endif
                            @if (!empty($data->mobile))
                            <p><span class="text-muted">Mobile:</span> {{ $data->mobile }}</p>    
                            @endif
                            @if (!empty($data->whatsapp_no))
                            <p><span class="text-muted">WhatsApp:</span> {{ $data->whatsapp_no }}</p>    
                            @endif
                           
                            <p class="small">  <p><span class="text-muted">Address : </span> {{ $data->address }}</p>
                            <p><span class="text-muted">Landmark :</span> {{ $data->landmark }}</p>
                            <p><span class="text-muted">City :</span> {{ $data->city }}</p>
                            <p><span class="text-muted">State :</span> {{ $data->state }}</p>
                            <p><span class="text-muted">Pincode :</span> {{ $data->pin }}</p>
                            <p><span class="text-muted">Aadhar Number :</span> {{ $data->aadhar_no }}</p>
                            
                                                   
                        </div>
                        <div class="col-md-4">
                            <p><b>Banking Details</b></p>
                            <p><span class="text-muted">A/C Holder Name :  </span> {{ $data->account_holder_name }}</p>
                            <p><span class="text-muted">Bank Name :  </span> {{ $data->bank_name }}</p>
                            <p><span class="text-muted">Branch Name :  </span> {{ $data->branch_name }}</p>
                            <p><span class="text-muted">A/C No : </span> {{ $data->bank_account_no }}</p>
                            <p><span class="text-muted">IFSC : </span> {{ $data->ifsc }}</p>
                            <p><b>Salary & Allowances</b></p>
                            <p><span class="text-muted">Monthly Salary: Rs. </span> {{ $data->monthly_salary }}</p>
                            <p><span class="text-muted">Daily Salary: Rs. </span> {{ $data->daily_salary }}</p>
                            <p><span class="text-muted">Travelling Allowance (Per Kilometer): Rs. </span> {{ $data->travelling_allowance }}</p>
                             
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="card shadow-sm">
                
                <div class="card shadow-sm">
                    <div class="card-header">
                        User ID Front
                    </div>
                    <div class="card-body">
                        <img src="{{asset($data->user_id_front)}}" alt="" style="height: 100px" class="mr-4">
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header">
                        User ID Back
                    </div>
                    <div class="card-body">
                        <img src="{{asset($data->user_id_back)}}" alt="" style="height: 100px" class="mr-4">
                    </div>
                </div>

                <div class="card-body text-end">
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-sm btn-danger">Back to Staff </a>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection
