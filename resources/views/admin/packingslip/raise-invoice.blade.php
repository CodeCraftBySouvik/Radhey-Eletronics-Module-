@extends('admin.layouts.app')
@section('page', 'Raise Invoice')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Sales</li>
        <li><a href="{{ route('admin.packingslip.index') }}">Packing Slips</a></li>
        <li>Raise Invoice</li>
    </ul>     
    <div class="row">
        <div class="col-sm-12">       
            @if (Session::has('message'))
                <div class="alert alert-success" role="alert">
                    {{ Session::get('message') }}
                </div>
            @endif

            @php
                $gstText = "GST";
                
                if($packingslips->order->is_gst == 0){
                    $gstText = "NON-GST";
                }
            @endphp

            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Order No:- <span>{{ $packingslips->order->order_no }} <strong>{{$gstText}}</strong> </span></h6>
                    <h6>Slip No:- <span>{{ $packingslips->slipno }}</span></h6>      
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group mb-3">
                                <h6>Customer Details</h6>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="small m-0">
                                            <strong>Person Name :</strong> 
                                        </p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="small m-0">
                                            {{$packingslips->store->store_name}}
                                        </p>
                                    </div>
                                </div>
                                @if (!empty($packingslips->store->bussiness_name))
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="small m-0">
                                            <strong>Company Name :</strong> 
                                        </p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="small m-0">
                                            {{$packingslips->store->bussiness_name}}
                                        </p>
                                    </div>
                                </div>    
                                @endif                               
                                @if (!empty($packingslips->store->email))
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="small m-0">
                                            <strong>Email ID :</strong> 
                                        </p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="small m-0">
                                            {{$packingslips->store->email}}
                                        </p>
                                    </div>
                                </div>    
                                @endif
                                @if (!empty($packingslips->store->contact))
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="small m-0">
                                            <strong>Mobile No :</strong> 
                                        </p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="small m-0">
                                            {{$packingslips->store->contact}}
                                        </p>
                                    </div>
                                </div>   
                                @endif                               
                                <div class="row">
                                    <div class="col-sm-3">
                                        <p class="small m-0">
                                            <strong>WhatsApp No :</strong> 
                                        </p>
                                    </div>
                                    <div class="col-sm-9">
                                        <p class="small m-0">
                                            {{$packingslips->store->whatsapp}}
                                        </p>
                                    </div>
                                </div>              
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <h6>
                                Billing Address
                                @if (!empty($packingslips->store->address_outstation))
                                    <span>(Outstation)</span>
                                @endif
                            </h6>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>Address :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->billing_address}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>Landmark :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->billing_landmark}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>State :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->billing_state}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>City :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->billing_city}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>Pin Code :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->billing_pin}}
                                    </p>
                                </div>
                            </div>                            
                        </div>
                        <div class="col-sm-4">
                            <h6>
                                Shipping Address
                                @if (!empty($packingslips->store->address_outstation))
                                    <span>(Outstation)</span>
                                @endif
                            </h6>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>Address :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->shipping_address}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>Landmark :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->shipping_landmark}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>State :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->shipping_state}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>City :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->shipping_city}}
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-3">
                                    <p class="small m-0">
                                        <strong>Pin Code :</strong> 
                                    </p>
                                </div>
                                <div class="col-sm-9">
                                    <p class="small m-0">
                                        {{$packingslips->store->shipping_city}}
                                    </p>
                                </div>
                            </div>                             
                        </div>
                    </div>
                </div>
            </div>
            <form id="myForm" action="{{ route('admin.packingslip.save_invoice') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="packingslip_id" value="{{$id}}">
            <input type="hidden" name="store_id" value="{{ $packingslips->store_id }}">
            <input type="hidden" name="user_id" value="{{ $packingslips->order->user_id }}">
            <input type="hidden" name="order_no" value="{{ $packingslips->order->order_no }}">
            <input type="hidden" name="slip_no" value="{{ $packingslips->slipno }}">
            <input type="hidden" name="order_id" value="{{ $packingslips->order_id }}">
            <input type="hidden" id="is_gst" name="is_gst" value="{{$packingslips->order->is_gst}}">

            
            
            <input type="hidden" name="store_address_outstation" value="{{ $packingslips->store->address_outstation }}">
            @php
                $net_price = 0;
            @endphp   
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Total Ctns</th>
                        <th>Total Pcs</th>
                        <th>Price per Piece (Exc.Tax)</th>
                        <th>Total Amount (Exc.Tax)</th>
                        <th>HSN Code</th>
                        @if (!empty($packingslips->store->address_outstation))
                        <th>IGST</th>   
                        @else
                        <th>CGST</th> 
                        <th>SGST</th>
                        @endif
                        <th>Total Amount (Inc.Tax)</th>                        
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @forelse ($packing_slip as $item)
                        
                    @php
                        

                        $getOrderProductDetails = getOrderProductDetails($packingslips->order_id,$item->product_id);
                        // $single_product_price = $getOrderProductDetails->price;
                        $single_product_price = $getOrderProductDetails->piece_price;
                        $product_qty = $getOrderProductDetails->qty;
                        $hsn_code = getSingleAttributeTable('products',$item->product_id,'hsn_code');
                        $igst = getSingleAttributeTable('products',$item->product_id,'igst');
                        $cgst = getSingleAttributeTable('products',$item->product_id,'cgst');
                        $sgst = getSingleAttributeTable('products',$item->product_id,'sgst');

                        if(!empty($packingslips->store->address_outstation)){
                            $gst_val = $igst;
                        } else {
                            $gst_val = ($cgst + $sgst);
                        }
                        
                        $getGSTAmount_single_product_price = getGSTAmount($single_product_price,$igst);
                        $single_pro_gst_amount = $getGSTAmount_single_product_price['gst_amount'];
                        $single_pro_net_amount = $getGSTAmount_single_product_price['net_price'];
                        
                        $total_price =  ( $single_pro_net_amount * $item->pcs );
                        
                        $gst_calculation = getPercentageVal($gst_val,$total_price);
                        $product_gst_price = ($total_price + $gst_calculation);
                                                
                        $net_price += $product_gst_price;
                    @endphp
                    <input type="hidden" name="details[{{$i}}][product_id]" value="{{ $item->product_id }}">
                    <input type="hidden" name="details[{{$i}}][product_name]" value="{{$item->product->name}}">
                    <input type="hidden" name="details[{{$i}}][is_store_address_outstation]" value="{{$packingslips->store->address_outstation}}">
                    <input type="hidden" name="details[{{$i}}][igst]" value="{{$igst}}">
                    <input type="hidden" name="details[{{$i}}][cgst]" value="{{$cgst}}">
                    <input type="hidden" name="details[{{$i}}][sgst]" value="{{$sgst}}">
                    <tr>   
                        <td>{{$i}}</td>                     
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->pcs }}</td>
                        <input type="hidden" name="details[{{$i}}][quantity]" value="{{$item->quantity}}">
                        <input type="hidden" name="details[{{$i}}][pcs]" value="{{$item->pcs}}">
                        <td>Rs. {{ number_format((float)$single_pro_net_amount, 2, '.', '') }}</td>
                        <input type="hidden" name="details[{{$i}}][price]" value="{{ number_format((float)$single_pro_net_amount, 2, '.', '') }}">
                        <input type="hidden" name="details[{{$i}}][single_product_price]" value="{{ number_format((float)$single_product_price, 2, '.', '') }}">
                        <td>Rs. {{ number_format((float)$total_price, 2, '.', '') }}</td>
                        <input type="hidden" name="details[{{$i}}][count_price]" value="{{ number_format((float)$total_price, 2, '.', '') }}">
                        <td>{{ $hsn_code }}</td>
                        <input type="hidden" name="details[{{$i}}][hsn_code]" value="{{$hsn_code}}">
                        @if (!empty($packingslips->store->address_outstation))
                            <td>{{$igst}} % </td>
                        @else
                            <td>{{$cgst}} % </td>
                            <td>{{$sgst}} % </td>
                        @endif
                        <td>Rs. {{ number_format((float)$product_gst_price, 2, '.', '') }}</td>
                        <input type="hidden" name="details[{{$i}}][total_price]" value="{{$product_gst_price}}">                           
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                        
                    @endforelse
                    
                </tbody>
                
            </table>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">                            
                            <h6>Total Invoice Amount (Inc. Tax) :- Rs. {{ number_format((float)$net_price, 2, '.', '') }}</h6>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" name="address_outstation" id="acknowledgement">
                                    <label class="form-check-label" for="acknowledgement">
                                        As per the Indian IT Act, an electronic document requires an electronic signature as prescribed by the Act, to gain legal sanctity in the court of law. Hence saying that the printed document in the subject is produced electronically and therefore does not require a signature is not acceptable.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($packingslips->store->address_outstation)     
            {{-- <div class="row">
                <div class="col-sm-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            Upload TRN (Store Address Outstation)
                            <span class="text-danger">*</span>
                        </div>
                        <div class="card-body">
                            <div class="w-100 product__thumb">
                            <label for="thumbnail"><img id="output" src="{{ asset('admin/images/placeholder-image.jpg') }}"/></label>
                            @error('trn_file') <p class="small text-danger">{{ $message }}</p> @enderror
                            </div>
                            <input type="hidden" name="is_trn_file_uploaded" value="0" id="is_trn_file_uploaded">
                            <input type="file" id="thumbnail" accept="image/*" name="trn_file" onchange="loadFile(event)" class="d-none" >
                            <script>
                            var loadFile = function(event) {
                                var output = document.getElementById('output');
                                output.src = URL.createObjectURL(event.target.files[0]);
                                output.onload = function() {
                                    URL.revokeObjectURL(output.src) // free memory
                                }
        
                                $('#is_trn_file_uploaded').val(1);
                            };
                            </script>
                        </div>
                    </div>     
                </div>
            </div>        --}}
              
            @endif
            <input type="hidden" name="net_price" value="{{$net_price}}">
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ route('admin.packingslip.index') }}" class="btn btn-sm btn-danger">Back</a>
                    <button type="submit" id="submitBtn" class="btn btn-sm btn-success"> Generate Invoice</button>
                </div>
            </div>
            </form>
        </div>
    </div>
    
</section>
@endsection

@section('script')
    <script>
        $("[type='number']").keypress(function (evt) {
            evt.preventDefault();
        });

        $(document).ready(function(){
            var is_gst = $('#is_gst').val();
            if(is_gst == 0){
                $('#myForm').submit();
            }
            $('#submitBtn').prop('disabled', true);
        })

        $('#acknowledgement').change(function(){
            var isCheck = $("#acknowledgement:checked").length;
            // alert(isCheck)
            if(isCheck == 1) {
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#submitBtn').prop('disabled', true);
            }
        })

        
    </script>
@endsection
