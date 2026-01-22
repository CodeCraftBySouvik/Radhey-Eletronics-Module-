@extends('admin.layouts.app')
@section('page', 'Add Purchase Return')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li><a href="{{ route('admin.purchasereturn.list') }}">Return</a></li>
        <li>Add Purchase Return</li>
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
    <form id="myForm" method="post" action="{{ route('admin.purchasereturn.save') }}">
    @csrf
        <div class="row">
            <div class="col-sm-4">
                <label for="">Supplier <span class="text-danger">*</span></label>
                
                <select class="form-control select-md"  name="supplier_id"  id="supplier_id">
                    <option  value="" selected hidden>Select a supplier </option>
                    @foreach ($suppliers as $item)
                    <option value="{{$item->id}}" @if(old('supplier_id') == $item->id) selected @endif>{{ $item->name }}</option>
                    @endforeach
                </select> 
                @error('supplier_id') <p class="small text-danger">{{ $message }}</p> @enderror  
            </div>            
            
            <div class="col-sm-8" id="prodDiv">
                <div class="table-responsive order-addmore" >
                    <table class="table table-sm"  id="timePriceTable">
                        <thead>
                            <tr>
                                <th width="600px">Product <span class="text-danger">*</span></th>
                                <th width="200px">Quantity (Ctns) <span class="text-danger">*</span></th>
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
                                        <input type="text" name="details[{{$key}}][product]"  placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$key}}" autocomplete="off" onkeyup="getProductByName(this.value, {{$key}});" value="{{ old('details.'.$key.'.product') }}"> 
                                        <input type="hidden" name="details[{{$key}}][product_id]" id="product_id{{$key}}" value="{{ old('details.'.$key.'.product_id') }}" class="productids">
                                        <div class="respDrop" id="respDrop{{$key}}"></div>
                                        @error('details.'.$key.'.product_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                    </td>
                                    
                                    <td>
                                        <input type="number"  name="details[{{$key}}][quantity]" id="quantity{{$key}}" class="form-control" value="{{ old('details.'.$key.'.quantity') }}" >
                                        @error('details.'.$key.'.quantity') <p class="small text-danger">{{ $message }}</p> @enderror
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
                                        <input type="text" name="details[1][product]" value="" placeholder="Search product by name ..." class="form-control select-md"  id="product_name1" onkeyup="getProductByName(this.value, 1);" autocomplete="off"> 
                                        <input type="hidden" name="details[1][product_id]" id="product_id1" class="productids">
                                        <div class="respDrop" id="respDrop1"></div>
                                    </td>
                                    <td>
                                        <input type="number" name="details[1][quantity]" id="quantity1" class="form-control" placeholder="Enter Quantity" value="1">
                                        
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
            </div>  
        </div>
        <div class="row">
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ route('admin.purchasereturn.list') }}" class="btn btn-danger select-md">Back</a>
                    <button type="submit" id="submitBtn" class="btn btn-success select-md">Add</button>
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

        $('#prodDiv').hide();
        $('#submitBtn').attr('disabled', true);

        @if(old('details'))
            $('#prodDiv').show();
            $('#submitBtn').attr('disabled', false);
            var order_amount = 0;
            $('.total_price').each(function(){
                if($(this).val() != ''){
                    order_amount += parseFloat($(this).val());
                }
            });
            
            $('#total_po_price').text(order_amount);

            $('.productids').each(function(){ 
                if($(this).val() != ''){
                    proIdArr.push($(this).val());
                }
            });
        @endif

        // alert(proIdArr)
        
    });

    $('#supplier_id').on('change', function(){
        $('#prodDiv').show();
        $('#submitBtn').attr('disabled', false);
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
                <input type="text" name="details[`+i+`][product]" value="" placeholder="Search product by name ..." class="form-control select-md"  id="product_name`+i+`" onkeyup="getProductByName(this.value, `+i+`);" autocomplete="off"> 
                <input type="hidden" name="details[`+i+`][product_id]" id="product_id`+i+`" class="productids">
                <div class="respDrop" id="respDrop`+i+`"></div>
            </td>
            <td>
                <input type="number" name="details[`+i+`][quantity]" id="quantity`+i+`" class="form-control" placeholder="Enter Quantity" value="1">
                
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
        var supplier_id = $('#supplier_id').val();  
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.product.searchByName') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: name,
                    supplier_id: supplier_id,
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
