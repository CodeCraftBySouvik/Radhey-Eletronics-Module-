@extends('admin.layouts.app')
@section('page',  'Edit Customer')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li><a href="{{ route('admin.store.index') }}">Customer Management</a> </li>
        <li>Edit Customer</li>
    </ul>
    <form method="post" id="" action="{{ route('admin.store.update', $data->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-sm-9">
                <div class="form-group mb-3">         
                    @error('type') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>
                <div class="card shadow-sm">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">Person Name <span class="text-danger">*</span></label>
                                <input type="text" name="store_name" autocomplete="off" class="form-control" value="{{$data->store_name}}">
                                @error('store_name') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>   
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">Company Name </label>
                                <input type="text" name="bussiness_name" autocomplete="off" class="form-control" value="{{$data->bussiness_name}}">
                                @error('bussiness_name') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>                    
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label class="label-control">Email </label>
                                <input type="text" name="email" autocomplete="off" class="form-control" value="{{$data->email}}">
                                @error('email') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">Mobile</label>
                                <input type="text" maxlength="11" name="contact" id="contact"  placeholder="User contact" class="form-control" value="{{$data->contact}}" onkeypress="validateNum(event)" autocomplete="off">
                                @error('contact') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <input type="hidden" name="is_wa_same" id="checkWhatsappHidden" value="0">
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" @if($data->is_wa_same == 1) checked @endif type="checkbox" value="1" name="is_wa_same" id="checkWhatsappSame">
                                    <label class="form-check-label" for="checkWhatsappSame">
                                      Same as Phone Number
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">Whatsapp no <span class="text-danger">*</span></label>
                                <input type="text" maxlength="10" name="whatsapp" id="whatsapp" placeholder="User whatsapp no" class="form-control" value="{{$data->whatsapp}}" onkeypress="validateNum(event)" autocomplete="off">
                                @error('whatsapp') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">GST Number </label>
                                <input type="text" name="gst_number" placeholder="GST Number" maxlength="50" class="form-control" value="{{$data->gst_number}}" autocomplete="off">
                            </div>
                        </div>                    
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">Credit Limit</label>
                                <input type="text" name="credit_limit" placeholder="Credit Limit" class="form-control" onkeypress="validateNum(event)" maxlength="20" value="{{$data->credit_limit}}" autocomplete="off">
                                @error('credit_limit') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <label for="">Credit days</label>
                                <input type="text" name="credit_days" placeholder="Credit Days" onkeypress="validateNum(event)" maxlength="5" class="form-control" value="{{$data->credit_days}}" autocomplete="off">
                                @error('credit_days') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        Address
                    </div>
                    <div class="card-body pt-0">
                        <div class="admin__content">
                            <aside>
                                
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-auto">
                                        <input type="hidden" name="address_outstation" value="0" id="addressOutstationHidden">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" name="address_outstation" @if($data->address_outstation == 1) checked @endif  id="addressOutstation">
                                            <label class="form-check-label" for="addressOutstation">
                                              Address as Outstation
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </content>
                        </div>
                        <div class="admin__content">
                            <aside>
                                <nav>Billing Address</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Address</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="billing_addr" class="form-control" aria-describedby="priceHelpInline" name="billing_address" value="{{$data->billing_address}}">
                                        @error('billing_address') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Landmark</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="billing_landmark" class="form-control" aria-describedby="priceHelpInline" name="billing_landmark" value="{{$data->billing_landmark}}">
                                        @error('billing_landmark') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>                                
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">City</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="billing_city" class="form-control" aria-describedby="priceHelpInline" name="billing_city" value="{{$data->billing_city}}">
                                        @error('billing_city') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-3 text-end">
                                        <label for="" class="col-form-label">State</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="billing_state" class="form-control" aria-describedby="priceHelpInline" name="billing_state" value="{{$data->billing_state}}">
                                        @error('billing_state') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>                          
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Country</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="billing_country" class="form-control" aria-describedby="priceHelpInline" name="billing_country" value="{{$data->billing_country}}">
                                        @error('billing_country') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-3 text-end">
                                        <label for="" class="col-form-label">Pincode</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="billing_pin" class="form-control" aria-describedby="priceHelpInline" name="billing_pin" value="{{$data->billing_pin}}" onkeypress="validateNum(event)">
                                        @error('billing_pin') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    
                                </div>
                            </content>
                        </div>
                        <div class="admin__content">
                            <aside>
                                
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <input type="hidden" name="is_billing_shipping_same" id="checkSameBillingHidden" value="0">
                                    <div class="col-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" @if($data->is_billing_shipping_same == 1) checked @endif name="is_billing_shipping_same" id="checkSameBilling">
                                            <label class="form-check-label" for="checkSameBilling">
                                              Same as Billing Address
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </content>
                        </div>
                        <div class="admin__content">
                            <aside>
                                <nav>Shipping Address</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Address</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="shipping_addr" class="form-control" aria-describedby="priceHelpInline" name="shipping_address" value="{{$data->shipping_address}}">
                                        @error('shipping_address') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Landmark</label>
                                    </div>
                                    <div class="col-9">
                                        <input type="text" id="shipping_landmark" class="form-control" aria-describedby="priceHelpInline" name="shipping_landmark" value="{{$data->shipping_landmark}}">
                                        @error('shipping_landmark') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">City</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="shipping_city" class="form-control" aria-describedby="priceHelpInline" name="shipping_city" value="{{$data->shipping_city}}">
                                        @error('shipping_city') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-3 text-end">
                                        <label for="" class="col-form-label">State</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="shipping_state" class="form-control" aria-describedby="priceHelpInline" name="shipping_state" value="{{$data->shipping_state}}">
                                        @error('shipping_state') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>                          
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Country</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="shipping_country" class="form-control" aria-describedby="priceHelpInline" name="shipping_country" value="{{$data->shipping_country}}">
                                        @error('shipping_country') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                    <div class="col-3 text-end">
                                        <label for="" class="col-form-label">Pincode</label>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" id="shipping_pin" class="form-control" aria-describedby="priceHelpInline" name="shipping_pin" value="{{$data->shipping_pin}}" onkeypress="validateNum(event)">
                                        @error('shipping_pin') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </content>
                        </div>
                    </div>
                </div>

                

            </div>
            <div class="col-sm-3">
                <div class="card shadow-sm">
                <div class="card-header">
                    Edit Store
                </div>
                <div class="card-body text-end">
                    <a href="{{ route('admin.store.index') }}" class="btn btn-sm btn-danger">Back</a>
                    <button type="submit" id="submitBtn" class="btn btn-sm btn-success">Update </button>
                </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-header">
                        GST certificate image
                    </div>
                    <div class="card-body">
                        <div class="w-100 product__thumb">                        
                        <label for="thumbnail"><img id="output" src="{{ asset($data->gst_file ??'admin/images/placeholder-image.jpg') }}"/></label>
                                               
                        @error('gst_file') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <input type="hidden" name="is_gst_file_uploaded" value="0" id="is_gst_file_uploaded">
                        <input type="file" id="thumbnail" accept="image/*" name="gst_file" onchange="loadFile(event)" class="d-none">
                        <script>
                        var loadFile = function(event) {
                            var output = document.getElementById('output');
                            output.src = URL.createObjectURL(event.target.files[0]);
                            output.onload = function() {
                            URL.revokeObjectURL(output.src) // free memory
                            }

                            $('#is_gst_file_uploaded').val(1);
                        };
                        </script>
                    </div>
                </div>

            </div>
        </div>
    </form>
</section>
@endsection

@section('script')
<script>
    $(document).ready(function(){
        var checkWhatsappSame = $('#checkWhatsappSame').val();
        var checkSameBilling = $('#checkSameBilling').val();

        var contactWhatsapp = $('#whatsapp').val().length;        
        if(contactWhatsapp >= 8){
            $('#submitBtn').prop('disabled',false);
            $('#checkWhatsappSame').attr('disabled',false);
        } else {
            $('#submitBtn').prop('disabled',true);
            $('#checkWhatsappSame').attr('disabled',true);
        }
        if(checkWhatsappSame == 1){
            $('#whatsapp').prop('readonly', true);      
        }
        if(checkSameBilling == 1){
            $('#shipping_addr').prop('readonly', true); 
            $('#shipping_landmark').prop('readonly', true); 
            $('#shipping_city').prop('readonly', true); 
            $('#shipping_state').prop('readonly', true); 
            $('#shipping_country').prop('readonly', true); 
            $('#shipping_pin').prop('readonly', true); 
        }
    })
    
    function validateNum(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
        // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\./;
        if( !regex.test(key) ) {
            theEvent.returnValue = false;
            if(theEvent.preventDefault) theEvent.preventDefault();
        }
    }

    $('#contact').on('keyup', function(){
        var contactLength = $('#contact').val().length;
        if(contactLength >= 8){
            $('#checkWhatsappSame').attr('disabled',false);
        }else{
            $('#checkWhatsappSame').attr('disabled',true);
        }
    })

    $('#whatsapp').on('keyup', function(){
        
        var contactWhatsapp = $('#whatsapp').val().length;
        
        if(contactWhatsapp >= 8){
            $('#submitBtn').prop('disabled',false);
        }else{
            $('#submitBtn').prop('disabled',true);
        }
    })
    

    $("input:checkbox#checkSameBilling").change(function() {
        var ischecked= $(this).is(':checked');
        var checkSameBillingHidden = $('#checkSameBillingHidden').val();

        var billing_addr = $('#billing_addr').val();
        var billing_landmark = $('#billing_landmark').val();
        var billing_city = $('#billing_city').val();
        var billing_state = $('#billing_state').val();
        var billing_country = $('#billing_country').val();
        var billing_pin = $('#billing_pin').val();

        if(ischecked){
            $('#shipping_addr').val(billing_addr);
            $('#shipping_addr').prop('readonly', true); 

            $('#shipping_landmark').val(billing_landmark);
            $('#shipping_landmark').prop('readonly', true); 

            $('#shipping_city').val(billing_city);
            $('#shipping_city').prop('readonly', true); 

            $('#shipping_state').val(billing_state);
            $('#shipping_state').prop('readonly', true); 

            $('#shipping_country').val(billing_country);
            $('#shipping_country').prop('readonly', true); 

            $('#shipping_pin').val(billing_pin);
            $('#shipping_pin').prop('readonly', true); 

            $('#checkSameBillingHidden').val(1);
        }else{
            $('#shipping_addr').val('');
            $('#shipping_addr').prop('readonly', false); 

            $('#shipping_landmark').val('');
            $('#shipping_landmark').prop('readonly', false); 

            $('#shipping_city').val('');
            $('#shipping_city').prop('readonly', false); 

            $('#shipping_state').val('');
            $('#shipping_state').prop('readonly', false); 

            $('#shipping_country').val('');
            $('#shipping_country').prop('readonly', false); 

            $('#shipping_pin').val('');
            $('#shipping_pin').prop('readonly', false); 

            $('#checkSameBillingHidden').val(0);
        }

       
    }); 

    $("input:checkbox#checkWhatsappSame").change(function() {
        var ischecked= $(this).is(':checked');
        var contact = $('#contact').val();
        var whatsapp = $('#whatsapp').val();  
        var checkWhatsappHidden = $('#checkWhatsappHidden').val();      

        if(ischecked){
            $('#whatsapp').val(contact);    
            $('#whatsapp').prop('readonly', true);  
            $('#checkWhatsappHidden').val(1);   
            $('#submitBtn').prop('disabled',false);         
        }else{
            $('#whatsapp').val('');    
            $('#whatsapp').prop('readonly', false);   
            $('#checkWhatsappHidden').val(0); 
            $('#submitBtn').prop('disabled',true);           
        }       
    });
    $("input:checkbox#addressOutstation").change(function(){
        var ischecked= $(this).is(':checked');
        var addressOutstationHidden = $('#addressOutstationHidden').val();    
        if(ischecked){ 
            $('#addressOutstationHidden').val(1);       
        }else{    
            $('#addressOutstationHidden').val(0);       
        }  
    })
</script>
@endsection
