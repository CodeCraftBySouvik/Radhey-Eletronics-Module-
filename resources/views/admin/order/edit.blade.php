@extends('admin.layouts.app')
@section('page', 'Edit Order')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Order</li>
        <li>Edit</li>
        <li>{{$order->order_no}}</li>
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
    @if (Session::has('message'))
        <div class="alert alert-success" role="alert">
            {{ Session::get('message') }}
        </div>
    @endif

    @if ($errors->first('thresholdErrMsg') != '')
    <form id="myForm" method="post" action="{{ route('admin.order.update-threshold',$id) }}">
    @else
    <form id="myForm" method="post" action="{{ route('admin.order.update',$id) }}">
    @endif
    
    @csrf
        <input type="hidden" name="user_id" value="{{ $order->user_id }}">
        <input type="hidden" name="store_id" id="store_id" value="{{ $order->store_id }}">
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
                                        <div class="col-sm-8"><p class="small m-0">{{ $order->stores->store_name }}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Company Name :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{ $order->stores->bussiness_name }}</p></div>
                                    </div>                                    
                                    
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <h6>Billing Address</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$order->stores->billing_address}}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Landmark :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$order->stores->billing_address}}</p></div>
                                    </div>                                    
                                    
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <h6>Billing Address</h6>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Address :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$order->stores->shipping_address}}</p></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-4"><p class="small m-0"><strong>Landmark :</strong></p></div>
                                        <div class="col-sm-8"><p class="small m-0">{{$order->stores->shipping_address}}</p></div>
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
                                <th>Pcs per Ctn <span class="text-danger">*</span></th>
                                <th>Price/Pcs (Inc.Tax) <span class="text-danger">*</span></th>
                                <th>No of Ctns <span class="text-danger">*</span></th>
                                <th>Total Pcs</th>
                                <th>Price/Ctn (Inc.Tax) </th>
                                <th rowspan="2">Total Amount (Inc.Tax)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(old('details'))
                            @php
                                $old_details = old('details');
                            @endphp
                            @foreach ($old_details as $key=>$details)
                            @php
                                $order_pro_det = getOrderProductDetails($id,$old_details[$key]['product_id']);
                                
                                $isOld = 0;
                                if(!empty($order_pro_det)){
                                    $isOld = 1;
                                } else {
                                    $isOld = 0;
                                }

                                
                            @endphp
                            <tr id="tr_{{$key}}" class="tr_pro">
                                <input type="hidden" name="details[{{$key}}][isOld]" value="{{$isOld}}">
                                <td>
                                    <input type="text" name="details[{{$key}}][product]"  placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$key}}" onkeyup="getProductByName(this.value, {{$key}});" value="{{ old('details.'.$key.'.product') }}" @if($isOld == 1) readonly @endif> 
                                    <input type="hidden" name="details[{{$key}}][product_id]" id="product_id{{$key}}" value="{{ old('details.'.$key.'.product_id') }}" class="productids">
                                    <div class="respDrop" id="respDrop{{$key}}"></div>
                                    @error('details.'.$key.'.product_id') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td>                                
                                <td>
                                    <input type="number"  name="details[{{$key}}][propcs]" id="propcs{{$key}}" class="form-control" value="{{ old('details.'.$key.'.propcs') }}" readonly>
                                    @error('details.'.$key.'.pcs') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td> 
                                <td>
                                    <input type="text"   name="details[{{$key}}][piece_price]" id="piece_price{{$key}}" class="form-control piece_price"  value="{{ old('details.'.$key.'.piece_price') }}" onkeyup="calculatePrice({{$key}})"  onkeypress="validateNum(event)" >
                                    @error('details.'.$key.'.piece_price') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td>     
                                <td>
                                    <input type="number" name="details[{{$key}}][qty]" id="qty{{$key}}" min="1" class="form-control" placeholder="Product qty"   onkeyup="if(value<0) value=0;calculatePrice({{$key}})" onchange="if(value<0) value=0;calculatePrice({{$key}})" value="{{ old('details.'.$key.'.qty') }}" >
                                    @error('details.'.$key.'.qty') <p class="small text-danger">{{ $message }}</p> @enderror
                                </td>
                                <td>
                                    <input type="text" name="details[{{$key}}][pcs]" readonly class="form-control"  id="pcs{{$key}}" placeholder="Total Pcs" value="{{ old('details.'.$key.'.pcs') }}">
                                </td>                          
                                <td>
                                    <input type="text" name="details[{{$key}}][price]"  id="price{{$key}}" class="form-control price"  placeholder="Product Cost Price" readonly value="{{ old('details.'.$key.'.price') }}">                                    
                                </td>
                                <td>
                                    <input type="text" readonly  name="details[{{$key}}][total_price]" id="total_price{{$key}}" class="form-control total_price" placeholder="Total Product Price" value="{{ old('details.'.$key.'.total_price') }}">
                                </td>
                                <td id="btn_td_{{$key}}">
                                    
                                    <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew{{$key}}">+</a>
                                    <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow({{$key}})" id="removeNew{{$key}}">X</a>
                                    <a class="btn btn-sm btn-secondary actionTimebtn " id="viewItem{{$key}}" onclick="return viewItem({{$key}});" title="View Item Details"><i class="fa fa-ellipsis-h"></i></a>
                                    
                                    
                                </td>
                            </tr>  
                            @endforeach
                            @else
                            @php
                                $i=1;
                            @endphp
                            @foreach ($items as $item)

                            @php
                                
                            @endphp
                            
                            <tr id="tr_{{$i}}" class="tr_pro">
                                <input type="hidden" name="details[{{$i}}][isOld]" value="1">
                                <td>
                                    <input readonly type="text" name="details[{{$i}}][product]" placeholder="Search product by name ..." class="form-control select-md"  id="product_name{{$i}}" value="{{$item->product_name}}" onkeyup="getProductByName(this.value, {{$i}});" > 
                                    <input type="hidden" name="details[{{$i}}][product_id]" id="product_id{{$i}}" value="{{$item->product_id}}" class="productids">
                                    <div class="respDrop" id="respDrop{{$i}}"></div>
                                </td>
                                <td>
                                    <input type="number"  name="details[{{$i}}][propcs]" id="propcs{{$i}}" class="form-control" value="{{ $item->pcs / $item->qty }}" readonly>
                                </td>
                                <td>
                                    <input type="text"   name="details[{{$i}}][piece_price]" id="piece_price{{$i}}" class="form-control piece_price" placeholder="Enter Price Per Piece" value="{{ $item->piece_price }}" onkeyup="calculatePrice({{$i}})"  onkeypress="validateNum(event)" >
                                </td>
                                <td>
                                    <input type="number"   name="details[{{$i}}][qty]" id="qty{{$i}}" min="1" class="form-control" placeholder="Product qty" value="{{ $item->qty }}" onkeyup="if(value<0) value=0;calculatePrice({{$i}})" onchange="if(value<0) value=0;calculatePrice({{$i}})" >
                                </td>
                                <td>
                                    <input type="number"   name="details[{{$i}}][pcs]" id="pcs{{$i}}" min="1" class="form-control" value="{{ $item->pcs }}" readonly>
                                </td>
                                <td>
                                    <input type="text"   name="details[{{$i}}][price]"  id="price{{$i}}" class="form-control price" value="{{ $item->price }}"  readonly>
                                </td>
                                <td>
                                    <input type="text" readonly  name="details[{{$i}}][total_price]" id="total_price{{$i}}" class="form-control total_price"  value="{{ $item->price * $item->qty }}">
                                </td>
                                <td id="btn_td_{{$i}}">
                                    
                                    <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew{{$i}}">+</a>
                                    <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow({{$i}})" id="removeNew{{$i}}">X</a>
                                    <a class="btn btn-sm btn-secondary actionTimebtn " id="viewItem{{$i}}" onclick="return viewItem({{$i}});" title="View Item Details"><i class="fa fa-ellipsis-h"></i></a>
                                    
                                    
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
                                                Rs. <span id="total_order_price">{{number_format((float)$order->amount, 2, '.', '')}}</span>
                                                <input type="hidden" name="amount" id="amount" value="@if(old('amount')){{old('amount')}}@else{{ $order->amount}}@endif">
                                            </h6>
                                        </td>
                                    </tr>                                    
                                    <tr class="border-top">                                        
                                        <td>
                                            <h6 class="text-dark mb-0 text-end"> 
                                                @error('thresholdErrMsgText') 
                                                    <span>
                                                        {{$message}}
                                                    </span>
                                                @enderror
                                            </h6>                                            
                                        </td>                                        
                                    </tr>
                                    <tr class="border-top">                                        
                                        <td>
                                            <h6 class="text-dark mb-0 text-end"> 
                                                @error('thresholdErrMsg') 
                                                    <span>
                                                        {{$message}}
                                                    </span>
                                                @enderror
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
                        <a href="{{ route('admin.order.index',['status'=>$order->status]) }}" class="btn btn-danger select-md">Back</a>
                        
                        @if ($errors->first('thresholdErrMsg') != '')
                        <button type="submit" id="submitBtn" class="btn btn-success select-md">Update and Proceed</button>
                        @else
                        <button type="submit" id="submitBtn" class="btn btn-success select-md">Update</button>
                        @endif
                        
                    </div>
                </div>
            </div>
            
        </div>
    </form>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prodTitle"></h5>
                    
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Last Three Order History</h6>
                    <div class="table-responsive">
                        <table class="table" id="prodHistTable">
                            <thead>
                                <th>Date</th>
                                <th>Price/Pcs</th>
                                <th>Quantity</th>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
                    </div>
                    <div id="other-det">

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script>

    var errMsgThr = "{{$errors->first('thresholdErrMsg')}}";

    if(errMsgThr != ''){
        $('input').attr('readonly', 'readonly');
        $('.addNewTime').hide();
        $('.removeTimePrice').hide();
    }
    
    var rowCount = $('#timePriceTable tbody tr').length;
    var proIdArr = [];
    $(document).ready(function(){          
        $('div.alert').delay(3000).slideUp(300);        

        $("#myForm").submit(function() {
            $('#submitBtn').attr('disabled', 'disabled');
            return true;
        });
        

        @if(old('details'))
            var order_amount = 0;
            $('.total_price').each(function(){
                if($(this).val() != ''){
                    order_amount += parseFloat($(this).val());
                }
            });
            $('#total_order_price').text(order_amount);
            $('#amount').val(order_amount);
        
        @endif
        $('.productids').each(function(){ 
            // alert($(this).val())
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

    var oldCount = "{{count($items)}}";
    oldCount = parseInt(oldCount)
    console.log(oldCount)
    var i = oldCount+1;
    
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
            <input type="hidden" name="details[`+i+`][isOld]" value="0">
            <td>
                <input type="text" name="details[`+i+`][product]" placeholder="Search product by name ..." class="form-control select-md"  id="product_name`+i+`" value="" onkeyup="getProductByName(this.value, `+i+`);"> 
                <input type="hidden" name="details[`+i+`][product_id]" id="product_id`+i+`" value="" class="productids">
                <div class="respDrop" id="respDrop`+i+`"></div>
            </td>
            <td>
                <input type="number"  name="details[`+i+`][propcs]" id="propcs`+i+`" class="form-control" value="" readonly>
            </td>
            <td>
                <input type="text" placeholder="Enter Price Per Piece"  name="details[`+i+`][piece_price]" id="piece_price`+i+`" class="form-control piece_price"  value="" onkeyup="calculatePrice(`+i+`)"  onkeypress="validateNum(event)">
            </td>
            <td>
                <input type="number"   name="details[`+i+`][qty]" id="qty`+i+`" min="1" class="form-control" placeholder="Product qty" value="1" onkeyup="if(value<0) value=0;calculatePrice(`+i+`)" onchange="if(value<0) value=0;calculatePrice(`+i+`)">
            </td>
            <td>
                <input type="number"  name="details[`+i+`][pcs]" id="pcs`+i+`" min="1" class="form-control" value="" readonly>
            </td>
            <td>
                <input type="text"   name="details[`+i+`][price]"  id="price`+i+`" class="form-control price" value="" readonly >
            </td>
            <td>
                <input type="text" readonly  name="details[`+i+`][total_price]" id="total_price`+i+`" class="form-control total_price" placeholder="Total Price" value="">
            </td>
            <td id="btn_td_`+i+`">
                <a class="btn btn-sm btn-success actionTimebtn addNewTime" id="addNew`+i+`">+</a>
                <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow(`+i+`)" id="removeNew`+i+`">X</a>
                <a class="btn btn-sm btn-secondary actionTimebtn " id="viewItem`+i+`" onclick="return viewItem(`+i+`);" title="View Item Details"><i class="fa fa-ellipsis-h"></i></a>
            </td>
        </tr>
        `;

        $('#timePriceTable tbody').append(toAppend);
        i++;
    });

    function removeRow(i){
        // alert(i);
        var count_tr_pro = $('.tr_pro').length;        
        // alert(count_tr_pro);
        if(count_tr_pro > 1){
            var total_price = $('#total_price'+i).val();
            var total_order_price = $('#total_order_price').html();
            var now_po_price = (total_order_price - total_price);
            
            var proId = $('#product_id'+i).val();                        
            proIdArr =  proIdArr.filter(e => e!=proId)

            $('#tr_'+i).remove();
            $('#total_order_price').text(now_po_price);
            $('#amount').val(now_po_price);
        }        
    }

    function financial(x) {
        return Number.parseFloat(x).toFixed(2).replace(/[.,]00$/, "");
    }

    function calculatePrice(number)
    {
        var propcs = $('#propcs'+number).val();
        var qty = $('#qty'+number).val();
        var totalPcs = (propcs * qty);
        $('#pcs'+number).val(totalPcs);

        
        // var piece_price = $('#piece_price'+number).val();
        // var price = (piece_price * totalPcs);
        // $('#price'+number).val(price);

        var piece_price = $('#piece_price'+number).val();
        var price = (propcs * piece_price)
        $('#price'+number).val(price);
        var total_price = (price * qty);

        total_price = financial(total_price);
        $('#total_price'+number).val(total_price);
        
        var sumPO = 0;
        $('.total_price').each(function(){
            sumPO += parseFloat($(this).val());
        });
        $('#total_order_price').text(sumPO);
        $('#amount').val(sumPO);
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
        $('.product-dropdown').hide();
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

                var ordHistory = result.last_three_order_product;
                console.log(ordHistory);
                var count_stock = result.count_stock;
                $('#exampleModal').modal('show');
                $('#prodTitle').html(result.name)
                var prodOrdHistoryHTML = ``;
                if(ordHistory.length > 0){
                    for(var i=0; i < ordHistory.length; i++)
                    prodOrdHistoryHTML += `
                    <tr>
                        <td>`+ordHistory[i].created_date+`</td>
                        <td>`+ordHistory[i].piece_price+`</td>
                        <td>`+ordHistory[i].qty+` ctns (`+ordHistory[i].pcs+` pcs)</td>
                    </tr>
                    `;
                } else {
                    prodOrdHistoryHTML += `
                    <tr colspan="3" style="text-align: center;">
                        <td>No sales amount found. <br/>Master Sell Price is Rs. `+result.sell_price+`</td>
                    </tr>
                    `;
                }

                prodOrdHistoryHTML += `
                    </tr>                    
                        <td> Current Stock:- <strong> `+count_stock+` </strong> ctns </td>
                    </tr>`;

                
                console.log(prodOrdHistoryHTML);
                $('#prodHistTable tbody').html(prodOrdHistoryHTML);var prodOrdHistoryHTML = otherDet = ``;
                if(ordHistory.length > 0){
                    for(var i=0; i < ordHistory.length; i++)
                    prodOrdHistoryHTML += `
                    <tr>
                        <td>`+ordHistory[i].created_date+`</td>
                        <td>`+ordHistory[i].piece_price+`</td>
                        <td>`+ordHistory[i].qty+` ctns (`+ordHistory[i].pcs+` pcs)</td>
                    </tr>
                    `;
                } else {
                    prodOrdHistoryHTML += `
                    <tr>
                        <td colspan="3" style="text-align: center;">No sales amount found.</td>
                    </tr>
                    `;
                    otherDet += `
                    <span>Master Sell Price is <strong>Rs. `+result.sell_price+`</strong></span> <br/>
                    
                    `;
                }
                otherDet += `
                    <span> Current Stock:- <strong> `+count_stock+` </strong> ctns </span> <br/>
                    `;                    
                $('#prodHistTable tbody').html(prodOrdHistoryHTML);
                $('#other-det').html(otherDet);


                var name = result.name;
                var pcs = result.pcs;
                var sell_price = result.sell_price;
                var piece_price = result.piece_price;
                var final_piece_price = piece_price;
                
                $('#product_name'+count).val(name);
                $('#product_id'+count).val(id);

                if(piece_price == 0){
                    final_piece_price = sell_price;
                }

                $('#piece_price'+count).val(final_piece_price);
                                
                $("#propcs" + count).val(pcs);
                var qty = $('#qty'+count).val();
                var price = (pcs * final_piece_price);
                $('#price'+count).val(price);
                $('#total_price'+count).val(price);
                var totalPcs = (pcs * qty);
                $('#pcs'+count).val(totalPcs);

                proIdArr.push(id);

                var order_amount = 0;
                $('.total_price').each(function(){
                    if($(this).val() != ''){
                        order_amount += parseFloat($(this).val());
                    }
                });
                $('#total_order_price').text(order_amount);
                $('#amount').val(order_amount);
                
            }
        });                
    }

    function viewItem(i){
        // alert(i);
        var productId = $('#product_id'+i).val();
        var store_id = $('#store_id').val();
        if(productId != ''){
            $.ajax({
                url: "{{ route('admin.product.viewDetail') }}",
                method: 'post',
                dataType: 'json',
                data: {
                    '_token': '{{ csrf_token() }}',
                    id: productId,
                    store_id: store_id
                },
                success: function(result) {
                    console.log(result);
                    var ordHistory = result.last_three_order_product;
                    console.log(ordHistory);
                    var count_stock = result.count_stock;
                    console.log('count_stock:- '+count_stock);
                    $('#exampleModal').modal('show');
                    $('#prodTitle').html(result.name)
                    var prodOrdHistoryHTML = otherDet = ``;
                    if(ordHistory.length > 0){
                        for(var i=0; i < ordHistory.length; i++)
                        prodOrdHistoryHTML += `
                        <tr>
                            <td>`+ordHistory[i].created_date+`</td>
                            <td>`+ordHistory[i].piece_price+`</td>
                            <td>`+ordHistory[i].qty+` ctns (`+ordHistory[i].pcs+` pcs)</td>
                        </tr>
                        `;
                    } else {
                        prodOrdHistoryHTML += `
                        <tr>
                            <td colspan="3" style="text-align: center;">No sales amount found.</td>
                        </tr>
                        `;
                        otherDet += `
                        <span>Master Sell Price is <strong>Rs. `+result.sell_price+`</strong></span> <br/>
                        
                        `;
                    }
                    otherDet += `
                        <span> Current Stock:- <strong> `+count_stock+` </strong> ctns </span> <br/>
                        `;                    
                    $('#prodHistTable tbody').html(prodOrdHistoryHTML);
                    $('#other-det').html(otherDet);
                    
                    
                }
            });
        } else {
            alert('Please Choose Product First');
        }
    }

</script>
@endsection
