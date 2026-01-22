@extends('admin.layouts.app')
@section('page', 'Edit GRN Amount')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>GRN</li>
        <li>Edit Amount</li>
    </ul>
    @if ($errors->any())
    {{-- <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div> --}}
    @endif
    <form id="myForm" method="post" action="{{ route('admin.grn.update-amount',$id) }}">
        @csrf
        <input type="hidden" name="purchase_order_id" value="{{ $data->purchase_order_id }}">
        
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    
                    <div class="col-sm-2">
                        <label for="">GRN NO</label>
                        <input type="text" name="grn_no" value="{{ $data->grn_no }}" class="form-control select-md" readonly id="">
                    </div>   
                    
                </div>                
            </div>                         
        </div>
        <div class="row">
            <div class="table-responsive order-addmore" >
                <table class="table table-sm" id="timePriceTable">
                    <h6>Item Details</h6>
                    <thead>
                        <tr>
                            <th width="400px">Product <span class="text-danger">*</span></th>
                            <th>Pcs per Ctn <span class="text-danger">*</span></th>
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
                            $pcs = getSingleAttributeTable('products',$old_details[$key]['product_id'],'pcs');
                        @endphp
                        <tr id="tr_{{$key}}" class="tr_pro">
                            <td>
                                
                                <input type="text" name="details[{{$key}}][product]"  placeholder="Search product by name ..." class="form-control select-md" id="product_name{{$key}}" onkeyup="getProductByName(this.value, {{$key}});" value="{{ old('details.'.$key.'.product') }}"> 
                                <input type="hidden" name="details[{{$key}}][product_id]" id="product_id{{$key}}" value="{{ old('details.'.$key.'.product_id') }}" class="productids">
                                <div class="respDrop" id="respDrop{{$key}}"></div>
                                @error('details.'.$key.'.product_id') <p class="small text-danger">{{ $message }}</p> @enderror
                            </td>
                            <td>
                                <input type="number" readonly name="details[{{$key}}][pcs]" id="pcs{{$key}}" class="form-control" value="{{ old('details.'.$key.'.pcs') }}" onkeyup="if(value<0) value=0;calculatePrice({{$key}})" onchange="if(value<0) value=0;calculatePrice({{$key}})">
                                @error('details.'.$key.'.pcs') <p class="small text-danger">{{ $message }}</p> @enderror
                            </td>
                            
                            <td>
                                <input type="text" name="details[{{$key}}][piece_price]"  id="piece_price{{$key}}" class="form-control piece_price"  placeholder="Product Cost Price" onkeyup="calculatePrice({{$key}})"  onkeypress="validateNum(event)" value="{{ old('details.'.$key.'.piece_price') }}">
                                @error('details.'.$key.'.piece_price') <p class="small text-danger">{{ $message }}</p> @enderror
                            </td>
                            <td>
                                <input type="number" name="details[{{$key}}][qty]" id="qty{{$key}}" min="1" class="form-control" placeholder="Product qty" readonly   onkeyup="if(value<0) value=0;calculatePrice({{$key}})" onchange="if(value<0) value=0;calculatePrice({{$key}})" value="{{ old('details.'.$key.'.qty') }}">
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
                        @foreach ($data->stock_product as $items)
                        @php
                            $pcs = getSingleAttributeTable('products',$items->product_id,'pcs');
                        @endphp
                       
                        <tr id="tr_{{$i}}" class="tr_pro">
                            <td>                                
                                <input type="text" name="details[{{$i}}][product]" value="{{$items->product->name}}" readonly placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$i}}" onkeyup="getProductByName(this.value, {{$i}});" > 
                                <input type="hidden" name="details[{{$i}}][product_id]" value="{{$items->product_id}}" id="product_id{{$i}}" class="productids">
                                <div class="respDrop" id="respDrop{{$i}}"></div>
                            </td>
                            <td>
                                <input type="number" readonly name="details[{{$i}}][pcs]" id="pcs{{$i}}" class="form-control" value="{{$pcs}}" onkeyup="if(value<0) value=0;calculatePrice({{$i}})" onchange="if(value<0) value=0;calculatePrice({{$i}})">
                            </td>
                            
                            <td>
                                <input type="text"   name="details[{{$i}}][piece_price]"  id="piece_price{{$i}}" class="form-control piece_price" value="{{$items->piece_price}}" placeholder="Product Cost Price" onkeyup="calculatePrice({{$i}})"  onkeypress="validateNum(event)">
                            </td>
                            <td>
                                <input type="number"  readonly name="details[{{$i}}][qty]" id="qty{{$i}}" min="1" class="form-control" placeholder="Product qty" value="{{$items->quantity}}" onkeyup="if(value<0) value=0;calculatePrice({{$i}})" onchange="if(value<0) value=0;calculatePrice({{$i}})">
                            </td>
                            <td>
                                <input type="text" name="details[{{$i}}][price_per_carton]" readonly class="form-control" value="{{$items->unit_price}}" id="price_per_carton{{$i}}" placeholder="Price per Carton">
                            </td>
                            <td>
                                <input type="text" readonly  name="details[{{$i}}][total_price]" id="total_price{{$i}}" class="form-control total_price" placeholder="Total Product Price" value="{{$items->total_price}}">
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
                    <div class="row mb-3 justify-content-end">
                        <div class="col-md-8">
                            <h6 class="text-muted mb-2">Total Amount (Inc.Tax)</h6>
                        </div>
                        <div class="col-md-4 text-end">
                            <table class="w-100">            
                                <tr class="border-top">
                                    <td>
                                        <h6 class="text-dark mb-0 text-end">
                                            Rs <span id="total_po_price">{{ number_format((float)$data->total_price, 2, '.', '') }}</span>
                                            <input type="hidden" name="amount" id="total_amount" value="{{$data->total_price}}">
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
                    <a href="{{ route('admin.returns.list') }}" class="btn btn-danger select-md">Back</a>
                    <button type="submit" id="submitBtn" class="btn btn-success select-md">Edit</button>
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
        // if(rowCount == 1){
        //     $('#removeNew1').hide();
        // }

        @if(old('details'))
            var order_amount = 0;
            $('.total_price').each(function(){
                if($(this).val() != ''){
                    order_amount += parseFloat($(this).val());
                }
            });
            $('#total_po_price').text(order_amount);
            $('#total_amount').val(order_amount);

            
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

    
    
    @if (old('details'))
           
        var totalDetails = "{{count(old('details'))}}"; 
        totalDetails = parseInt(totalDetails)    
        console.log('totalDetails:- '+totalDetails);
        i = totalDetails+1;    
    @else 
    totalDetails = "{{count($data->stock_product)}}";   
        totalDetails = parseInt(totalDetails)   
        i = totalDetails+1;    
    @endif

    console.log('index:- '+i);


    function financial(x) {
        return Number.parseFloat(x).toFixed(2).replace(/[.,]00$/, "");
    }

    function calculatePrice(number)
    {       
        var pcs = $('#pcs'+number).val();
        var piece_price = $('#piece_price'+number).val();
        var qty = $('#qty'+number).val();

        var oldQty = $('#oldQty'+number).val();
        var isOld = $('#isOld'+number).val();
        
        var isNoCtnChanged = 0;
        if(isOld != 0){
            if(oldQty != qty){
                isNoCtnChanged = 1;
            }
        }
        $('#isNoCtnChanged'+number).val(isNoCtnChanged);

        var price_per_carton = (pcs * piece_price) ; 
        $('#price_per_carton'+number).val(price_per_carton);

        var new_price_per_ctn = $('#price_per_carton'+number).val();
        var totalPrice = (new_price_per_ctn * qty);
        // var productPrice = (price_per_carton / pcs);
        totalPrice = financial(totalPrice);
        $('#total_price'+number).val(totalPrice);
        
        var sumPO = 0;
        $('.total_price').each(function(){
            if($(this).val() != ''){
                sumPO += parseFloat($(this).val());
            }
            
        });
        $('#total_po_price').text(sumPO);
        $('#total_amount').val(sumPO);
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
        var store_id = $('#store_id').val();
        $.ajax({
            url: "{{ route('admin.product.viewDetail') }}",
            method: 'post',
            data: {
                '_token': '{{ csrf_token() }}',
                id: id,
                store_id: store_id
            },
            success: function(result) {
                console.log(result);
                var name = result.name;
                var hsn_code = result.hsn_code;
                // var cost_price = result.cost_price;
                var piece_price = result.piece_price;
                var pcs = result.pcs;
                
                $('#product_name'+count).val(name);
                $('#product_id'+count).val(id);
                
                $("#hsn_code" + count).val(hsn_code);
                $("#pcs" + count).val(pcs);
                $('#piece_price'+count).val(piece_price);

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
                    if($(this).val() != ''){
                        sumPO += parseFloat($(this).val());
                    }
                    
                });
                $('#total_po_price').text(sumPO);
                $('#total_amount').val(sumPO);

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
