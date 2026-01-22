@extends('admin.layouts.app')

@section('page', 'Create Purchase Order')

@section('content')

<section>

    <ul class="breadcrumb_menu">

        <li>Purchase Order</li>

        <li><a href="{{ route('admin.purchaseorder.index') }}?type=po">PO</a></li>

        <li>Create Purchase Order</li>

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

    <form id="myForm" method="post" action="{{ route('admin.purchaseorder.store') }}">

    @csrf

        <div class="row">

            <div class="col-sm-12">

                <div class="col-sm-4">

                    <label for="">Supplier</label>

                    @if (!empty($supplier_id))

                    <input type="hidden" name="supplier_id" value="{{$supplier_id}}">

                    <select class="form-control select-md" disabled name="" required id="supplier_id">

                        

                        @foreach ($suppliers as $item)

                        <option value="{{$item->id}}"  @if(isset($supplier_id) && $supplier_id == $item->id) selected @endif  >{{ $item->name }}</option>

                        @endforeach

                    </select> 

                    @else

                    <select class="form-control select-md"  name="supplier_id" required id="supplier_id">

                        <option  value="" selected hidden>Select a supplier</option>

                        @foreach ($suppliers as $item)

                        <option value="{{$item->id}}" >{{ $item->name }}</option>

                        @endforeach

                    </select> 

                    @endif

                      

                    {{-- <p class="small text-danger">*** Please choose supplier first and then proceed for adding product items</p>                 --}}

                </div>            

                <input type="hidden" required name="unique_id" id="unique_id">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="row">                            

                            <div class="col-sm-4">

                                <div class="form-group mb-3">

                                    <h6>Depot Address</h6>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{ $settings?$settings->po_order_address : '0' }}</p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>City :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{ $settings? $settings->po_order_city : '0'}}</p></div>

                                    </div>                                    

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>State :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$settings? $settings->po_order_state : '0' }} </p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Country :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{ $settings? $settings->po_order_country : '0' }} </p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Pincode :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$settings? $settings->po_order_pin : '0'}} </p></div>

                                    </div>

                                    <input type="hidden" name="address" value="{{$settings? $settings->po_order_address : '0'}}">

                                    <input type="hidden" name="city" value="{{$settings?  $settings->po_order_city : '0' }}">

                                    <input type="hidden" name="state" value="{{$settings? $settings->po_order_state : '0' }}">

                                    <input type="hidden" name="country" value="{{ $settings? $settings->po_order_country : '0' }}">

                                    <input type="hidden" name="pin" value="{{$settings?  $settings->po_order_pin : '0'}}">

                                </div>

                            </div>

                            @if (!empty($supplier_id))

                            <div class="col-sm-4">

                                <div class="form-group mb-3">

                                    <h6>Supplier Billing Address</h6>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->billing_address}}</p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Landmark :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->billing_landmark}}</p></div>

                                    </div>                                    

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>City :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->billing_city}} </p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>State :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->billing_state}} </p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Country :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->billing_country}} </p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Pincode :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->billing_pin}} </p></div>

                                    </div>

                                </div>

                            </div>

                            <div class="col-sm-4">

                                <div class="form-group mb-3">

                                    <h6>Supplier Shipping Address</h6>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->shipping_address}}</p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Landmark :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->shipping_landmark}}</p></div>

                                    </div>                                    

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>City :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->shipping_city}} </p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>State :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->shipping_state}} </p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Country :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->shipping_country}} </p></div>

                                    </div>

                                    <div class="row">

                                        <div class="col-sm-4"><p class="small m-0"><strong>Pincode :</strong></p></div>

                                        <div class="col-sm-8"><p class="small m-0">{{$supplier_details->shipping_pin}} </p></div>

                                    </div>

                                </div>

                            </div>   

                            @endif

                            

                        </div>

                    </div>

                </div>

                {{-- <p class="small text-danger">*** Please make sure if change supplier in middle of adding product items all added items will be lost.</p>  --}}

                <div class="table-responsive order-addmore" >

                    <table class="table table-sm" @if(empty($supplier_id)) style="display: none;" @endif id="timePriceTable">

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

                            <tr id="tr_{{$key}}" class="tr_pro">

                                <td>

                                    <input type="text" name="details[{{$key}}][product]"  placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$key}}" onkeyup="getProductByName(this.value, {{$key}});" value="{{ old('details.'.$key.'.product') }}"> 

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

                                            <input type="text" class="form-control"  name="details[{{$key}}][weight]" id="weight{{$key}}" maxlength="5" onkeypress="validateNum(event)" placeholder="Enter carton weight">

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

                                <td id="btn_td_{{$key}}">

                                    <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew{{$key}}">+</a>

                                    <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow({{$key}})" id="removeNew{{$key}}">X</a>

                                </td>

                            </tr>  

                            @endforeach

                            @else

                            <tr id="tr_1" class="tr_pro">

                                <td>

                                    <input type="text" name="details[1][product]" value="" placeholder="Search product by name ..." class="form-control select-md"  id="product_name1" onkeyup="getProductByName(this.value, 1);" > 

                                    <input type="hidden" name="details[1][product_id]" id="product_id1" class="productids">

                                    <div class="respDrop" id="respDrop1"></div>

                                </td>

                                <td>

                                    <input type="text" name="details[1][hsn_code]" id="hsn_code1" class="form-control" placeholder="Enter HSN Code" maxlength="6">

                                    

                                </td>

                                <td>

                                    <input type="number"  name="details[1][pcs]" id="pcs1" class="form-control" value="1" onkeyup="if(value<0) value=0;calculatePrice(1)" onchange="if(value<0) value=0;calculatePrice(1)">

                                </td>

                                {{-- <td>

                                    <div class="row">

                                        <div class="col-sm-8">

                                            <input type="text" class="form-control"  name="details[1][weight]" id="weight1" maxlength="5" onkeypress="validateNum(event)" placeholder="Enter carton weight">

                                        </div>

                                        <div class="col-sm-4">

                                            <select name="details[1][weight_unit]" id="weight_unit1" class="form-control">

                                                <option value="kg" selected>KG</option>

                                                <option value="g">G</option>

                                            </select>

                                        </div>

                                    </div>

                                </td> --}}

                                <td>

                                    <input type="text"   name="details[1][piece_price]"  id="piece_price1" class="form-control piece_price" value="" placeholder="Product Cost Price" onkeyup="calculatePrice(1)"  onkeypress="validateNum(event)">

                                </td>

                                <td>

                                    <input type="number"   name="details[1][qty]" id="qty1" min="1" class="form-control" placeholder="Product qty" value="1" onkeyup="if(value<0) value=0;calculatePrice(1)" onchange="if(value<0) value=0;calculatePrice(1)">

                                </td>

                                <td>

                                    <input type="text" name="details[1][price_per_carton]" readonly class="form-control"  id="price_per_carton1" placeholder="Price per Carton">

                                </td>

                                <td>

                                    <input type="text" readonly  name="details[1][total_price]" id="total_price1" class="form-control total_price" placeholder="Total Product Price" value="">

                                </td>

                                <td id="btn_td_1">

                                    <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew1">+</a>

                                    <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow(1)" id="removeNew1">X</a>

                                </td>

                            </tr>  

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

                                        <td>

                                            <h6 class="text-dark mb-0 text-end"> Rs <span id="total_po_price">0</span></h6>

                                            

                                        </td>

                                    </tr>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>   

                <div class="card shadow-sm">

                    <div class="card-body">

                        <a href="{{ route('admin.purchaseorder.index') }}" class="btn btn-danger select-md">Back</a>

                        <a href="{{ route('admin.purchaseorder.create') }}" class="btn btn-warning select-md">Reset Form</a>

                        <a href="{{ route('admin.purchaseorder.create',$supplier_id) }}" class="btn btn-warning select-md">Reset Items</a>

                        <button type="submit" id="submitBtn" class="btn btn-success select-md">Add</button>

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

        $('#total_po_price').text(order_amount);



        $('.productids').each(function(){ 

            proIdArr.push($(this).val())

        });

        @endif



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



    $('#supplier_id').on('change', function(){

        // console.log(this.value);

        var supplier_id = $('#supplier_id').val();

        // $.ajax({

        //     url: "{{ route('admin.purchaseorder.getProductsSupplier') }}",

        //     dataType: 'JSON',

        //     type: 'POST',

        //     data: {

        //         'supplier_id': this.value,

        //         '_token': '{{ csrf_token() }}'

        //     },

        //     success: function(data){

        //         // console.log(data);

        //         var proHtml = ``;

        //         proHtml += `<option value="">Select a product</option>`;

        //         for(var i=0;i < data.length; i++){

        //             proHtml += `<option value="`+data[i].id+`" data-id="`+data[i].id+`">`+data[i].name+`</option>`;

        //         }





        //         $('.product-dropdown').html(proHtml);



                

                

        //     }

        // });

        console.log(`{{ url('admin/purchaseorder/create/`+supplier_id+`') }}`);

        window.location = `{{ url('admin/purchaseorder/create/`+supplier_id+`') }}`;

        

    }); 



    $("#unique_id").val(Date.now());

    var i = 2;

    @if (old('details'))

        // {{count(old('details'))}}          

        @foreach($old_details as $key=>$details)

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

            <td>

                <input type="text" name="details[`+i+`][product]" value="" placeholder="Search product by name ..." class="form-control select-md"  id="product_name`+i+`" onkeyup="getProductByName(this.value, `+i+`);" > 

                <input type="hidden" name="details[`+i+`][product_id]" id="product_id`+i+`" class="productids">

                <div class="respDrop" id="respDrop`+i+`"></div>

            </td>

            <td>

                <input type="text"  name="details[`+i+`][hsn_code]" id="hsn_code`+i+`" class="form-control" placeholder="Enter HSN Code" maxlength="6">

                

            </td>

            <td>

                <input type="number"  name="details[`+i+`][pcs]" id="pcs`+i+`" class="form-control" value="1" onkeyup="if(value<0) value=0;calculatePrice(`+i+`)" onchange="if(value<0) value=0;calculatePrice(`+i+`)">

            </td>

            

            <td>

                <input type="text"   name="details[`+i+`][piece_price]"  id="piece_price`+i+`" class="form-control piece_price" value="" placeholder="Product Cost Price" onkeyup="calculatePrice(`+i+`)"  onkeypress="validateNum(event)">

            </td>

            <td>

                <input type="number"   name="details[`+i+`][qty]" id="qty`+i+`" min="1" class="form-control" placeholder="Product qty" value="1" onkeyup="if(value<0) value=0;calculatePrice(`+i+`)" onchange="if(value<0) value=0;calculatePrice(`+i+`)">

            </td>

            <td>

                <input type="text" name="details[`+i+`][price_per_carton]" readonly class="form-control"  id="price_per_carton`+i+`" placeholder="Price per Carton">

            </td>

            <td>

                <input type="text" readonly  name="details[`+i+`][total_price]" id="total_price`+i+`" class="form-control total_price" placeholder="Total Product Price" value="">

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

            var total_po_price = $('#total_po_price').html();

            var now_po_price = (total_po_price - total_price);

            

            var proId = $('#product_id'+i).val();                        

            proIdArr =  proIdArr.filter(e => e!=proId)



            $('#tr_'+i).remove();

            $('#total_po_price').html(now_po_price);

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

                var qty = $('#qty'+count).val();

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

