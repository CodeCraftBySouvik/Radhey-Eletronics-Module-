@extends('admin.layouts.app')
@section('page', 'Edit Invoice')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Invoice</li>
        <li>{{$invoice->invoice_no}}</li>
        <li>Edit</li>
    </ul>
    <ul class="breadcrumb_menu">
        <li>Packing Slip</li>
        <li>{{$invoice->packingslip->slipno}}</li>
    </ul>
    <ul class="breadcrumb_menu">
        <li>Order</li>
        <li>{{$invoice->order->order_no}}</li>
    </ul>
    {{-- @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif --}}
    <form id="myForm" method="post" action="{{ route('admin.invoice.update') }}">
        @csrf
        <input type="hidden" name="invoice_id" value="{{$id}}">
        <input type="hidden" name="slip_no" value="{{$invoice->packingslip->slipno}}">
        <input type="hidden" name="packingslip_id" value="{{$invoice->packingslip_id}}">
        <input type="hidden" name="order_id" value="{{$invoice->order_id}}">
        <div class="row">
            <div class="col-sm-12">
                <div class="col-sm-4">
                    <label for="">Customer</label>
                </div>            
                
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">                            
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <h6>Details</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Name :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{ $invoice->store->store_name }}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Company Name :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{ $invoice->store->bussiness_name }}</p></div>
                                    </div>                                    
                                    
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <h6>Billing Address</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$invoice->store->billing_address}}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Landmark :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$invoice->store->billing_address}}</p></div>
                                    </div>                                    
                                    
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <h6>Shipping Address</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$invoice->store->shipping_address}}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Landmark :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$invoice->store->shipping_address}}</p></div>
                                    </div>                                    
                                    
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
                <div class="table-responsive order-addmore" >
                    <table class="table table-sm" id="timePriceTable">
                        <h6>Item Details</h6>
                        <thead>
                            <tr>
                                <th width="400px">Product <span class="text-danger">*</span></th>
                                <th>HSN</th>
                                <th>Pcs per Ctn <span class="text-danger">*</span></th>
                                <th>Stock In Ctn</th>
                                <th>Price/Ctn (Inc.Tax) <span class="text-danger">*</span></th>
                                <th>No of Ctns <span class="text-danger">*</span></th>
                                <th>Total Pcs</th>
                                <th colspan="2">Total Amount (Inc.Tax)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (old('details'))
                                @php
                                    $old_details = old('details');
                                @endphp
                                @foreach ($old_details as $key=>$details)
                                @php
                                    $getInvoiceProducts = getInvoiceProducts($id,$old_details[$key]['product_id']);
                                    // echo '<pre>'; print_r($getInvoiceProducts);
                                    $isOld = 0;
                                    $quantity = 0;
                                    $count_stock = 0;
                                    if(!empty($getInvoiceProducts)){
                                        $isOld = 1;
                                        $quantity = $getInvoiceProducts->quantity;
                                        $count_stock = $getInvoiceProducts->count_stock;
                                    }
                                    
                                @endphp
                                <tr id="tr_{{$key}}" class="tr_pro">
                                    <input type="hidden" name="details[{{$key}}][oldCtnNo]" id="oldCtnNo{{$key}}" value="{{$quantity}}">
                                    <input type="hidden" name="details[{{$key}}][isNoCtnChanged]" id="isNoCtnChanged{{$key}}" value="">
                                    <input type="hidden" name="details[{{$key}}][price]" id="price{{$key}}" value="{{old('details.'.$key.'.price')}}">
                                    <input type="hidden" name="details[{{$key}}][count_price]" id="count_price{{$key}}" value="{{old('details.'.$key.'.count_price')}}">
                                    <td>
                                        <input type="text" name="details[{{$key}}][product]" placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$key}}" value="{{ old('details.'.$key.'.product') }}" onkeyup="getProductByName(this.value, {{$key}});" @if($isOld == 1) readonly @endif> 
                                        <input type="hidden" name="details[{{$key}}][product_id]" id="product_id{{$key}}" value="{{ old('details.'.$key.'.product_id') }}" class="productids">
                                        <div class="respDrop" id="respDrop{{$key}}"></div>
                                        @error('details.'.$key.'.product_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </td>
                                    <input type="hidden" name="details[{{$key}}][igst]" id="igst{{$key}}" value="{{ old('details.'.$key.'.igst') }}">
                                    <input type="hidden" name="details[{{$key}}][sgst]" id="sgst{{$key}}" value="{{ old('details.'.$key.'.sgst') }}">
                                    <input type="hidden" name="details[{{$key}}][cgst]" id="cgst{{$key}}" value="{{ old('details.'.$key.'.cgst') }}">
                                    <td>
                                        <input type="text" value="{{ old('details.'.$key.'.hsn_code') }}" name="details[{{$key}}][hsn_code]" class="form-control"  id="hsn_code{{$key}}">
                                        @error('details.'.$key.'.hsn_code') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </td>
                                    <td>
                                        <input type="number"  name="details[{{$key}}][propcs]" id="propcs{{$key}}" class="form-control" value="{{ old('details.'.$key.'.propcs') }}" readonly>
                                        @error('details.'.$key.'.propcs') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </td>
                                    <td>
                                        <input type="text" name="" readonly class="form-control" id="stock{{$key}}" value="{{$count_stock}}">
                                    </td>
                                    <td>
                                        <input type="text"   name="details[{{$key}}][piece_price]"  id="piece_price{{$key}}" class="form-control piece_price" value="{{ old('details.'.$key.'.piece_price') }}" placeholder="Enter price per carton" onkeyup="calculatePrice({{$key}})"  onkeypress="validateNum(event)">
                                        @error('details.'.$key.'.piece_price') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </td>
                                    <td>
                                        <input type="number"   name="details[{{$key}}][quantity]" id="quantity{{$key}}" min="1" class="form-control" placeholder="Enter number of cartons" value="{{ old('details.'.$key.'.quantity') }}" onkeyup="if(value<0) value=0;calculatePrice({{$key}})" onchange="if(value<0) value=0;calculatePrice({{$key}})">
                                        @error('details.'.$key.'.quantity') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </td>
                                    <td>
                                        <input type="number"   name="details[{{$key}}][pcs]" id="pcs{{$key}}" min="1" class="form-control" value="{{ old('details.'.$key.'.pcs') }}" readonly>
                                        
                                    </td>
                                    <td>
                                        <input type="text" readonly  name="details[{{$key}}][total_price]" id="total_price{{$key}}" class="form-control total_price" placeholder="Total Price" value="{{ old('details.'.$key.'.total_price') }}">
                                        @error('details.'.$key.'.total_price') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </td>
                                    <td id="btn_td_{{$key}}">
                                        <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew{{$key}}">+</a>
                                        <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow({{$key}})" id="removeNew{{$key}}">X</a>
                                    </td>
                                </tr>  
                                @endforeach
                            @else
                                @php
                                    $i=1;
                                @endphp
                                @foreach ($invoice_products as $item)
                                @php
                                    $getInvoiceProducts = getInvoiceProducts($id,$item->product_id);
                                    
                                    $count_stock = $getInvoiceProducts->count_stock;
                                    $price = $getInvoiceProducts->price;
                                    $count_price = $getInvoiceProducts->count_price;
                                @endphp
                                <tr id="tr_{{$i}}" class="tr_pro">
                                    <input type="hidden" name="details[{{$i}}][oldCtnNo]" id="oldCtnNo{{$i}}" value="{{$item->quantity}}">
                                    <input type="hidden" name="details[{{$i}}][isNoCtnChanged]" id="isNoCtnChanged{{$i}}" value="0">
                                    <input type="hidden" name="details[{{$i}}][price]" id="price{{$i}}" value="{{$price}}">
                                    <input type="hidden" name="details[{{$i}}][count_price]" id="count_price{{$i}}" value="{{$count_price}}">
                                    <td>
                                        <input type="text" name="details[{{$i}}][product]" placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$i}}" value="{{$item->product_name}}" onkeyup="getProductByName(this.value, {{$i}});" readonly> 
                                        <input type="hidden" name="details[{{$i}}][product_id]" id="product_id{{$i}}" value="{{$item->product_id}}" class="productids">
                                        <div class="respDrop" id="respDrop{{$i}}"></div>
                                    </td>
                                    <input type="hidden" name="details[{{$i}}][igst]" id="igst{{$i}}" value="{{$item->igst}}">
                                    <input type="hidden" name="details[{{$i}}][sgst]" id="sgst{{$i}}" value="{{$item->sgst}}">
                                    <input type="hidden" name="details[{{$i}}][cgst]" id="cgst{{$i}}" value="{{$item->cgst}}">
                                    <td>
                                        <input type="text" value="{{$item->hsn_code}}" name="details[{{$i}}][hsn_code]" class="form-control"  id="hsn_code{{$i}}">
                                    </td>
                                    <td>
                                        <input type="number"  name="details[{{$i}}][propcs]" id="propcs{{$i}}" class="form-control" value="{{ $item->pcs / $item->quantity }}" readonly>
                                    </td>
                                    <td>
                                        <input type="text" name="" value="{{$count_stock}}" readonly class="form-control" id="stock{{$i}}">
                                    </td>
                                    <td>
                                        <input type="text"   name="details[{{$i}}][piece_price]"  id="piece_price{{$i}}" class="form-control piece_price" value="{{ $item->single_product_price }}" placeholder="Enter price per carton" onkeyup="calculatePrice({{$i}})"  onkeypress="validateNum(event)">
                                    </td>
                                    <td>
                                        <input type="number"   name="details[{{$i}}][quantity]" id="quantity{{$i}}" min="1" class="form-control" placeholder="Enter number of cartons" value="{{ $item->quantity }}" onkeyup="if(value<0) value=0;calculatePrice({{$i}})" onchange="if(value<0) value=0;calculatePrice({{$i}})">
                                    </td>
                                    <td>
                                        <input type="number"   name="details[{{$i}}][pcs]" id="pcs{{$i}}" min="1" class="form-control" value="{{ $item->pcs }}" readonly>
                                    </td>
                                    <td>
                                        <input type="text" readonly  name="details[{{$i}}][total_price]" id="total_price{{$i}}" class="form-control total_price" placeholder="Total Price" value="{{ $item->single_product_price * $item->pcs }}">
                                    </td>
                                    <td id="btn_td_{{$i}}">
                                        <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew{{$i}}">+</a>
                                        <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow({{$i}})" id="removeNew{{$i}}">X</a>
                                    </td>
                                </tr>  
                                @php
                                    $i++;
                                @endphp 
                                @endforeach
                            @endif
                            
                            
                        </tbody>
                    </table>
                </div>                
                
                <div class="card shadow-sm">
                    <div class="card-body"> 
                        <p>** Please remember, Cartons and Prices will be set as final order product carton and price</p>
                        <div class="row mb-3 justify-content-end">
                            <div class="col-md-8">
                                <h6 class="text-muted mb-2">Total Invoice Amount (Inc.Tax)</h6>
                            </div>
                            <div class="col-md-4 text-end">
                                <table class="w-100">            
                                    <tr class="border-top">
                                        <td>
                                            <h6 class="text-dark mb-0 text-end">
                                                Rs <span id="total_inv_price">{{ number_format((float)$invoice->net_price, 2, '.', '') }}</span>
                                                <input type="hidden" name="net_price" id="net_price" value="{{$invoice->net_price}}">
                                            </h6>
                                            
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>   
                <div class="card shadow-sm">
                    <div class="card-body">
                        <a href="{{ route('admin.invoice.index') }}" class="btn btn-danger select-md">Back</a>
                        
                        <button type="submit" id="submitBtn" class="btn btn-success select-md">Update</button>
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
        // alert(rowCount)
        if(rowCount == 1){
            $('#removeNew1').hide();
        }

        @if(old('details'))
        var order_amount = 0;
        $('.total_price').each(function(){
            if($(this).val() != ''){
                order_amount += parseFloat($(this).val());
            }
        });
        order_amount = parseFloat(order_amount).toFixed(2)
        $('#total_inv_price').text(order_amount);
        $('#net_price').val(order_amount);
        @endif

        $('.productids').each(function(){ 
            if($(this).val() != ''){
                proIdArr.push($(this).val())
            }
            
        });

        // alert(proIdArr)
        
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

    var oldCount = "{{count($invoice_products)}}";
    oldCount = parseInt(oldCount)
    console.log(oldCount)
    var i = oldCount+1;
    @if (old('details'))
        // {{count(old('details'))}}          
        @foreach(old('details') as $key=>$details)
            var totalDetails = "{{$key}}";
        @endforeach        
        // var totalDetails = "{{count(old('details'))}}"; 
        totalDetails = parseInt(totalDetails)    
        console.log('totalDetails:- '+totalDetails);
        i = totalDetails+1;        
    @endif

    console.log('index:- '+i);
    
    $(document).on('click','.addNewTime',function(){
        var thisClickedBtn = $(this);
        
        var toAppend = `
        <tr id="tr_`+i+`" class="tr_pro">
            <input type="hidden" name="details[`+i+`][oldCtnNo]" id="oldCtnNo`+i+`" value="">
            <input type="hidden" name="details[`+i+`][isNoCtnChanged]" id="isNoCtnChanged`+i+`" value="0">
            <input type="hidden" name="details[`+i+`][price]" id="price`+i+`" value="">
            <input type="hidden" name="details[`+i+`][count_price]" id="count_price`+i+`" value="">
            <td>
                <input type="text" name="details[`+i+`][product]" placeholder="Search product by name ..." class="form-control select-md"  id="product_name`+i+`" value="" onkeyup="getProductByName(this.value, `+i+`);" > 
                <input type="hidden" name="details[`+i+`][product_id]" id="product_id`+i+`" value="" class="productids">
                <div class="respDrop" id="respDrop`+i+`"></div>
            </td>
            <input type="hidden" name="details[`+i+`][igst]" id="igst`+i+`">
            <input type="hidden" name="details[`+i+`][sgst]" id="sgst`+i+`">
            <input type="hidden" name="details[`+i+`][cgst]" id="cgst`+i+`">
            <td>
                <input type="text" name="details[`+i+`][hsn_code]" class="form-control"  id="hsn_code`+i+`">
            </td>
            <td>
                <input type="number"  name="details[`+i+`][propcs]" id="propcs`+i+`" class="form-control" value="" readonly>
            </td>
            <td>
                <input type="text" name="" readonly class="form-control" id="stock`+i+`">
            </td>
            <td>
                <input type="text"   name="details[`+i+`][piece_price]"  id="piece_price`+i+`" class="form-control piece_price" value="" placeholder="Enter price per carton" onkeyup="calculatePrice(`+i+`)"  onkeypress="validateNum(event)">
            </td>
            <td>
                <input type="number"   name="details[`+i+`][quantity]" id="quantity`+i+`" min="1" class="form-control" placeholder="Enter number of cartons" value="1" onkeyup="if(value<0) value=0;calculatePrice(`+i+`)" onchange="if(value<0) value=0;calculatePrice(`+i+`)">
            </td>
            <td>
                <input type="number"   name="details[`+i+`][pcs]" id="pcs`+i+`" min="1" class="form-control" value="" readonly>
            </td>
            <td>
                <input type="text" readonly  name="details[`+i+`][total_price]" id="total_price`+i+`" class="form-control total_price" placeholder="Total Price" value="">
            </td>
            <td id="btn_td_`+i+`">
                <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew`+i+`">+</a>
                <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow(`+i+`)" id="removeNew`+i+`">X</a>
            </td>
        </tr>  
        `;

        $('#timePriceTable').append(toAppend);
        i++;
    });

    
    function removeRow(i){
        // alert(i);
        var count_tr_pro = $('.tr_pro').length;        
        // alert(count_tr_pro);
        if(count_tr_pro > 1){
            var total_price = $('#total_price'+i).val();
            var total_inv_price = $('#total_inv_price').html();
            
            var now_po_price = (total_inv_price - total_price);
            
            var proId = $('#product_id'+i).val();                        
            proIdArr =  proIdArr.filter(e => e!=proId)

            $('#tr_'+i).remove();
            $('#total_inv_price').html(now_po_price);
            $('#net_price').val(now_po_price);
        }        
    }

    function financial(x) {
        return Number.parseFloat(x).toFixed(2).replace(/[.,]00$/, "");
    }

    function calculatePrice(number)
    {
        var stock = $('#stock'+number).val();        
        var piece_price = $('#piece_price'+number).val();
        var quantity = $('#quantity'+number).val();  
        var propcs = $('#propcs'+number).val();
        var pcs = (quantity * propcs);
        $('#pcs'+number).val(pcs);
        
        console.log('stock:- '+stock);
        console.log('quantity:- '+ $('#quantity'+number).val());
        quantity = parseInt(quantity);
        stock = parseInt(stock);
        
        if(quantity > stock ){
            alert('Cannot add more from stock quantity');
            $('#quantity'+number).val(stock); 
            $('#pcs'+number).val((stock*propcs)); 
            return true;
        }

        var price = $('#price'+number).val();
        var count_price = (pcs * price)
        // count_price = parseFloat(count_price).toFixed(2);
        $('#count_price'+number).val(count_price);
        
        var oldCtnNo = $('#oldCtnNo'+number).val();
        if(oldCtnNo != ''){
            if(quantity != oldCtnNo){
                $('#isNoCtnChanged'+number).val(1);
            } else {
                $('#isNoCtnChanged'+number).val(0);
            }
        }

        var igst = $('#igst'+number).val();
        var gstnetPrice = getGSTAmount(piece_price,igst);
        
        $('#price'+number).val(gstnetPrice);

        var total_price = (pcs*piece_price);
        $('#total_price'+number).val(total_price);
        
        total_price = financial(total_price);
        $('#total_price'+number).val(total_price);
        
        var sumPO = 0;
        $('.total_price').each(function(){
            if($(this).val() != ''){
                sumPO += parseFloat($(this).val());
            }            
        });
        sumPO = parseFloat(sumPO).toFixed(2)
        $('#total_inv_price').text(sumPO);
        $('#net_price').val(sumPO);
    }
    
    function getProductByName(name, count) {  
        if(name.length > 0) {
            // alert(proIdArr)
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
                var pcs = result.pcs;
                var igst = result.igst;
                var sgst = result.sgst;
                var cgst = result.cgst;
                var count_stock = result.count_stock;
                
                $('#product_name'+count).val(name);
                $('#product_id'+count).val(id);                
                $("#hsn_code" + count).val(hsn_code);
                $("#propcs" + count).val(pcs);
                $('#igst'+count).val(igst);
                $('#sgst'+count).val(sgst);
                $('#cgst'+count).val(cgst);
                $('#pcs'+count).val(pcs);
                $('#stock'+count).val(count_stock);
                proIdArr.push(id);

                $('#piece_price'+count).val('');
                $('#quantity'+count).val(1);
                $('#pcs'+count).val(pcs);
                $('#total_price'+count).val('');


                var order_amount = 0;
                $('.total_price').each(function(){
                    if($(this).val() != ''){
                        order_amount += parseFloat($(this).val());
                    }
                });
                order_amount = parseFloat(order_amount).toFixed(2)
                $('#total_inv_price').text(order_amount);
                $('#net_price').val(order_amount);
                
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

    function getGSTAmount(costPrice,gstVal){
        var gstAmount = costPrice - ( costPrice * ( 100 / ( 100 + ( (gstVal / 100) * 100 ) ) ) );
        var netPrice = (costPrice - gstAmount);
        netPrice = parseFloat(netPrice).toFixed(2);

        return netPrice;
    }

</script>
@endsection