@extends('admin.layouts.app')
@section('page', 'Edit Purchase Order')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li><a href="{{ route('admin.purchaseorder.index') }}">All POs</a></li>
        <li>Edit Purchase Order</li>
    </ul>
    <form id="myForm" method="post" action="{{ route('admin.purchaseorder.update') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="unique_id" value="{{$po->unique_id}}">
    <input type="hidden" name="id" value="{{$po->id}}">
        <div class="row">
            <div class="col-sm-12">
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <input type="hidden" name="supplier_id" required value="{{$po->supplier_id}}">
                        <input type="text" disabled class="form-control select-md" value="{{$po->supplier->name}}" id="">
                        
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">                            
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <h6>Depot Address</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{ $po->address }}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>City :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{ $po->city }}</p></div>
                                    </div>                                    
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>State :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{ $po->state }} </p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Country :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{ $po->country }} </p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Pincode :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{ $po->pin }} </p></div>
                                    </div>
                                    <input type="hidden" name="address" value="{{ $po->address }}">
                                    <input type="hidden" name="city" value="{{ $po->city }}">
                                    <input type="hidden" name="state" value="{{ $po->state }}">
                                    <input type="hidden" name="country" value="{{ $po->country }}">
                                    <input type="hidden" name="pin" value="{{ $po->pin }}">
                                </div>
                            </div>
                            @if (!empty($po->supplier_id))
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <h6>Supplier Billing Address</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->billing_address}}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Landmark :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->billing_landmark}}</p></div>
                                    </div>                                    
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>City :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->billing_city}} </p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>State :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->billing_state}} </p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Country :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->billing_country}} </p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Pincode :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->billing_pin}} </p></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <h6>Supplier Shipping Address</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->shipping_address}}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Landmark :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->shipping_landmark}}</p></div>
                                    </div>                                    
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>City :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->shipping_city}} </p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>State :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->shipping_state}} </p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Country :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->shipping_country}} </p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Pincode :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$po->supplier->shipping_pin}} </p></div>
                                    </div>
                                </div>
                            </div>   
                            @endif
                            
                        </div>
                    </div>
                </div>
               <div class="table-responsive">
                <table class="table table-sm" id="timePriceTable">
                    <h6>Item Details</h6>
                    <thead>
                        <tr>
                            <th width="400px">Product <span class="text-danger">*</span></th>
                            <th>HSN Code <span class="text-danger">*</span></th>
                            <th>Pcs per Ctn <span class="text-danger">*</span></th>
                            {{-- <th rowspan="2">Weight per Ctn <span class="text-danger">*</span></th> --}}
                            <th>Price/Pc (Inc.Tax) <span class="text-danger">*</span></th>
                            <th>No of Ctns <span class="text-danger">*</span></th>
                            <th>Price Per Ctn (Inc.Tax)</th>
                            <th colspan="2">Total Amount (Inc.Tax)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(old('details'))
                            @php
                                $old_details = old('details');
                            @endphp
                            @foreach ($old_details as $key=>$details)
                            @php
                                $getPODetails = getPODetails($po->id,$old_details[$key]['product_id']);
                                $qty = $getPODetails->qty;
                            @endphp
                            <tr id="tr_{{$key}}" class="tr_pro">
                                <input type="hidden" name="details[{{$key}}][oldCtnNo]" id="oldCtnNo{{$key}}" value="{{$qty}}">
                                <input type="hidden" name="details[{{$key}}][isNoCtnChanged]" id="isNoCtnChanged{{$key}}" value="">
                                
                                <td>
                                    <input type="text" name="details[{{$key}}][product]"  placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$key}}" onkeyup="getProductByName(this.value, {{$key}});" value="{{ old('details.'.$key.'.product') }}" readonly> 
                                    <input type="hidden" name="details[{{$key}}][product_id]" id="product_id{{$key}}" value="{{ old('details.'.$key.'.product_id') }}" class="productids">
                                    <div class="respDrop" id="respDrop{{$key}}"></div>
                                    @error('details.'.$key.'.product_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td>
                                <td>
                                    <input type="text"  name="details[{{$key}}][hsn_code]" id="hsn_code{{$key}}" class="form-control" placeholder="Enter HSN Code" value="{{ old('details.'.$key.'.hsn_code') }}" maxlength="6">
                                    @error('details.'.$key.'.hsn_code') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td>
                                <td>
                                    <input type="number"  name="details[{{$key}}][pcs]" id="pcs{{$key}}" class="form-control" value="{{ old('details.'.$key.'.pcs') }}" onkeyup="if(value<0) value=0;calculatePrice({{$key}})" onchange="if(value<0) value=0;calculatePrice({{$key}})">
                                    @error('details.'.$key.'.pcs') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td>
                                {{-- <td>
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control"  name="details[{{$key}}][weight]" id="weight{{$key}}" maxlength="5" onkeypress="validateNum(event)" placeholder="Enter carton weight" value="{{ old('details.'.$key.'.weight') }}">
                                            @error('details.'.$key.'.weight') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                        <div class="col-sm-4">
                                            <select name="details[{{$key}}][weight_unit]"  id="weight_unit{{$key}}" class="form-control">
                                                <option value="kg" @if(old('details.'.$key.'.weight_unit') == 'kg') selected @endif>KG</option>
                                                <option value="g" @if(old('details.'.$key.'.weight_unit') == 'g') selected @endif>G</option>
                                            </select>
                                            @error('details.'.$key.'.weight_unit') <p class="small text-danger">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                </td> --}}
                                <td>
                                    <input type="text" name="details[{{$key}}][piece_price]"  id="piece_price{{$key}}" class="form-control piece_price"  placeholder="Product Cost Price" onkeyup="calculatePrice({{$key}})"  onkeypress="validateNum(event)" value="{{ old('details.'.$key.'.piece_price') }}">
                                    @error('details.'.$key.'.piece_price') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td>
                                <td>
                                    <input type="number" name="details[{{$key}}][qty]" id="qty{{$key}}" min="1" class="form-control" placeholder="Product qty"   onkeyup="if(value<0) value=0;calculatePrice({{$key}})" onchange="if(value<0) value=0;calculatePrice({{$key}})" value="{{ old('details.'.$key.'.qty') }}">
                                    @error('details.'.$key.'.qty') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td>
                                <td>
                                    <input type="text" name="details[{{$key}}][price_per_carton]" readonly class="form-control"  id="price_per_carton{{$key}}" placeholder="Price per Carton" value="{{ old('details.'.$key.'.price_per_carton') }}">
                                </td>
                                <td>
                                    <input type="text" readonly  name="details[{{$key}}][total_price]" id="total_price{{$key}}" class="form-control total_price" placeholder="Total Product Price" value="{{ old('details.'.$key.'.total_price') }}">
                                </td>
                                
                            </tr>  
                            @endforeach
                        @else
                        
                            @php                            
                                $i = 1;
                            @endphp
                            @forelse ($data as $item)
                            <tr id="tr_{{$i}}" class="tr_pro">
                                <input type="hidden" name="details[{{$i}}][oldCtnNo]" id="oldCtnNo{{$i}}" value="{{$item->qty}}">
                                <input type="hidden" name="details[{{$i}}][isNoCtnChanged]" id="isNoCtnChanged{{$i}}" value="0">
                                
                                <td>
                                    <input type="text" name="details[{{$i}}][product]" value="{{$item->product}}" placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$i}}" onkeyup="getProductByName(this.value, {{$i}});" readonly> 
                                    <input type="hidden" name="details[{{$i}}][product_id]" id="product_id{{$i}}" class="productids" value="{{$item->product_id}}">
                                    <div class="respDrop" id="respDrop{{$i}}"></div>
                                </td>
                                <td>
                                    <input type="text" name="details[{{$i}}][hsn_code]" id="hsn_code{{$i}}" class="form-control" placeholder="Enter HSN Code" maxlength="6" value="{{$item->hsn_code}}">
                                    
                                </td>
                                <td>
                                    <input type="number"  name="details[{{$i}}][pcs]" id="pcs{{$i}}" class="form-control"  onkeyup="if(value<0) value=0;calculatePrice({{$i}})" onchange="if(value<0) value=0;calculatePrice({{$i}})" value="{{$item->pcs}}">
                                </td>
                                {{-- <td>
                                    <div class="row g-1">
                                        <div class="col-8">
                                            <input type="text" class="form-control"  name="details[{{$i}}][weight]" id="weight{{$i}}" maxlength="5" onkeypress="validateNum(event)" placeholder="Enter carton weight" value="{{$item->weight}}">
                                        </div>
                                        <div class="col-4">
                                            <select name="details[{{$i}}][weight_unit]" id="weight_unit{{$i}}" class="form-control">
                                                <option value="kg" @if($item->weight_unit == 'kg') selected @endif>KG</option>
                                                <option value="g" @if($item->weight_unit == 'g') selected @endif>G</option>
                                            </select>
                                        </div>
                                    </div>
                                </td> --}}
                                <td>
                                    <input type="text"   name="details[{{$i}}][piece_price]"  id="piece_price{{$i}}" class="form-control piece_price" value="{{$item->piece_price}}" placeholder="Product Cost Price" onkeyup="calculatePrice({{$i}})"  onkeypress="validateNum(event)">
                                </td>
                                <td>
                                    <input type="number"   name="details[{{$i}}][qty]" id="qty{{$i}}" min="1" class="form-control" placeholder="Product qty" value="{{$item->qty}}" onkeyup="if(value<0) value=0;calculatePrice({{$i}})" onchange="if(value<0) value=0;calculatePrice({{$i}})">
                                </td>
                                <td>
                                    <input type="text" name="details[{{$i}}][price_per_carton]" readonly class="form-control"  id="price_per_carton{{$i}}" placeholder="Price per Carton" value="{{$item->unit_price}}">
                                </td>
                                <td>
                                    <input type="text" readonly  name="details[{{$i}}][total_price]" id="total_price{{$i}}" class="form-control total_price" placeholder="Total Product Price" value="{{$item->total_price}}">
                                </td>
                                
                            </tr>  
                            @php
                                $i++;
                            @endphp
                            @empty
                                
                            @endforelse
                        
                        @endif
                    </tbody>
                </table>
               </div>
                <div class="card shadow-sm">
                    <div class="card-body">  
                        <div class="row mb-3 justify-content-end">
                            <div class="col-md-8">
                                <h6 class="text-muted mb-2">Total Amount (Inc.Tax)</h6>
                            </div>
                            <div class="col-md-4 text-end">
                                <table class="w-100">            
                                    <tr class="border-top">
                                        <td><h6 class="text-dark mb-0 text-end"> Rs <span id="total_po_price">0</span></h6></td>
                                        <input type="hidden" name="total_po_price" id="total_po_price_val" value="{{$po->total_price}}">
                                    </tr>
                                </table>
                            </div>
                        </div>    
                    </div>
                </div>       
                <div class="card shadow-sm">
                    <div class="card-body">
                        <a href="{{ route('admin.purchaseorder.index') }}" class="btn btn-sm btn-danger">Back</a>
                        <button id="submitBtn" type="submit" class="btn btn-sm btn-success">Update </button>
                        
                    </div>
                </div>
            </div>
            
        </div>
    </form>
</section>
@endsection

@section('script')
<script>
    var rowCount = $('#timePriceTable tbody tr').length;
    var proIdArr = [];
    $(document).ready(function(){
        if(rowCount == 1){
            $('#removeNew1').hide();
        }

        @if(old('details'))
            var sumPO = 0;
            $('.total_price').each(function(){
                if($(this).val() != ''){
                    sumPO += parseFloat($(this).val());
                }
            });
            $('#total_po_price').text(sumPO); 
            $('#total_po_price_val').val(sumPO);           
        @else
            
            var sumPO = 0;
            $('.total_price').each(function(){
                sumPO += parseFloat($(this).val());
            });
            $('#total_po_price').text(sumPO);
            $('#total_po_price_val').val(sumPO);
        @endif

        $('.productids').each(function(){ 
            proIdArr.push($(this).val())
        });
        
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
    
    var i = "{{ count($data)+1 }}";
    @if(old('details'))
        @foreach($old_details as $key=>$details)
            var totalDetails = "{{$key}}";
        @endforeach        
        // var totalDetails = "{{count(old('details'))}}"; 
        totalDetails = parseInt(totalDetails)    
        console.log('totalDetails:- '+totalDetails);
        i = totalDetails+1;   
    @endif
    
    function removeRow(i){
        var count_tr_pro = $('.tr_pro').length;
        if(count_tr_pro > 1){
            var total_price = $('#total_price'+i).val();
            var total_po_price = $('#total_po_price').html();            
            var now_po_price = (total_po_price - total_price);
            
            var proId = $('#product_id'+i).val();                        
            proIdArr =  proIdArr.filter(e => e!=proId)

            $('#tr_'+i).remove();
            $('#total_po_price').html(now_po_price);
            $('#total_po_price_val').val(now_po_price);
        }        
    }

    function financial(x) {
        return Number.parseFloat(x).toFixed(2).replace(/[.,]00$/, "");
    }

    function calculatePrice(number)
    {
        // alert(number);
        /* Clear previous output */   
        // $('#total_price'+number).val('');

        /* Calculate new entry */
        var pcs = $('#pcs'+number).val();
        var piece_price = $('#piece_price'+number).val();
        var qty = $('#qty'+number).val();
        var oldCtnNo = $('#oldCtnNo'+number).val();

        if(qty != oldCtnNo){
            $('#isNoCtnChanged'+number).val(1);
        }

        var price_per_carton = (pcs * piece_price) ; 
        $('#price_per_carton'+number).val(price_per_carton);

        var new_price_per_ctn = $('#price_per_carton'+number).val();
        var totalPrice = (new_price_per_ctn * qty);
        // var productPrice = (price_per_carton / pcs);
        totalPrice = financial(totalPrice);
        $('#total_price'+number).val(totalPrice);
        
        var sumPO = 0;
        $('.total_price').each(function(){
            sumPO += parseFloat($(this).val());
        });
        $('#total_po_price').text(sumPO);
        $('#total_po_price_val').val(sumPO);
        // alert(sumPO);        
    }   
    
    function getProductByName(name, count) {  
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.product.searchByName') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: name,
                    idnotin: proIdArr
                },
                success: function(result) {
                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 product-dropdown select-md" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchProduct('${count}',${value.id})">${value.name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 product-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No product found</li></div>`;
                    }
                    $('#respDrop'+count).html(content);
                }
            });
        }   else {
            $('.product-dropdown').hide()
        }   
        
    }

    function fetchProduct(count,id) {
        $('.product-dropdown').hide()
        $.ajax({
            url: "{{ route('admin.product.viewDetail') }}",
            method: 'post',
            data: {
                '_token': '{{ csrf_token() }}',
                id: id
            },
            success: function(result) {
                console.log(result);
                var name = result.name;
                var hsn_code = result.hsn_code;
                var cost_price = result.cost_price;
                var pcs = result.pcs;
                
                $('#product_name'+count).val(name);
                $('#product_id'+count).val(id);
                $("#hsn_code" + count).val(hsn_code);
                $("#pcs" + count).val(pcs);
                $('#piece_price'+count).val(cost_price);

                var piece_price = $('#piece_price'+count).val();
                var  qty = $('#qty'+count).val();
                var pcs = $('#pcs'+count).val();

                var price_per_carton = (pcs * piece_price) ; 
                $('#price_per_carton'+count).val(price_per_carton);

                var new_price_per_ctn = $('#price_per_carton'+count).val();
                var totalPrice = (new_price_per_ctn * qty);
                totalPrice = financial(totalPrice);

                $('#total_price'+count).val(totalPrice);        
                $('#removeNew'+count).show();

                var sumPO = 0;
                $('.total_price').each(function(){
                    sumPO += parseFloat($(this).val());
                });
                $('#total_po_price').text(sumPO);
                $('#total_po_price_val').val(sumPO);

                proIdArr.push(id);
                
            }
        });    
                
    }

    $(document).ready(function(){
        $("#myForm").submit(function() {
            // $('input').attr('disabled', 'disabled');
            $('#submitBtn').attr('disabled', 'disabled');
            return true;
        });
    })
</script>
@endsection
