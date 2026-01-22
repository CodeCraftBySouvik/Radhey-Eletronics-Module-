@extends('admin.layouts.app')
@section('page', 'Edit Packing Slip')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Order:- {{$packingslip->order->order_no}}</li>
        <li>Packing Slip:- {{$packingslip->slipno}}</li>
    </ul>
    <ul class="breadcrumb_menu">
        <li>Store</li>
        <li>{{$packingslip->store->bussiness_name}}</li>
        <li>{{$packingslip->store->store_name}}</li>
    </ul>
    <div class="row">
        <div class="col-sm-12">  
            {{-- @if($errors->any())                      
                {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
            @endif --}}
            @if (Session::has('message'))
                <div class="alert alert-success" role="alert">
                    {{ Session::get('message') }}
                </div>
            @endif
            <form id="myForm" action="{{ route('admin.packingslip.update',$id) }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{$packingslip->order_id}}">
            
            @if($errors->any())            
                {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
            @endif
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Required Ctn</th>
                            <th>Disburse Ctn</th>
                            <th>Disbursed</th>
                            <th>Total Stock</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i=1;
                            $totalCtns = 0;
                            $totalPcs = 0;
                        @endphp
                        @forelse ($packing_slip as $item)
                        @php
                            $checkStockPO = checkStockPO($item->product_id,$item->quantity);
                            $stockStatus = $checkStockPO['status'];
                            $maxStock = $checkStockPO['stock'];
                            $is_stock = $checkStockPO['is_stock'];
                            $pieces = $checkStockPO['pieces'];

                            $getOrderProductDetails = getOrderProductDetails($packingslip->order_id,$item->product_id);
                            $qty = $getOrderProductDetails->qty;
                            $pcs = $getOrderProductDetails->pcs;
                            $totalCtns += $item->quantity;
                            $totalPcs += $pcs;

                            $propcs = getSingleAttributeTable('products',$item->product_id,'pcs')
                        @endphp
                        <tr id="row{{$i}}" class="tr_pro">
                            <td>{{$i}}</td>
                            <td>
                                <span>{{$item->product->name}}</span>
                            </td>
                            <td>
                                {{$qty}} ({{$pcs}} pcs)
                            </td>
                            <input type="hidden" name="details[{{$i}}][is_disbursed]" value="{{$item->is_disbursed}}">                        
                            <td>
                                <input type="number" value="{{ $item->quantity }}" name="details[{{$i}}][quantity]" max="{{$maxStock}}" min="1" class="form-control" id="quantity{{$i}}" onkeydown="getQuantity({{$i}});" onkeyup="getQuantity({{$i}});" onkeypress="getQuantity({{$i}});" onchange="getQuantity({{$i}});">
                                <input type="hidden" name="" value="{{ $item->quantity }}" id="oldQty{{$i}}" class="oldQty">
                                <input type="hidden" name="details[{{$i}}][maxstock]" value="{{$maxStock}}" id="maxstock{{$i}}">
                                <input type="hidden" name="details[{{$i}}][pcs]" value="{{$pcs}}" id="pcs{{$i}}" class="oldPcs">
                                <input type="hidden" name="details[{{$i}}][product_id]" value="{{$item->product_id}}">
                                <input type="hidden" name="details[{{$i}}][isChanged]" id="isChanged{{$i}}" value="0">
                                <input type="hidden" name="details[{{$i}}][propcs]" id="propcs{{$i}}" value="{{$propcs}}">
                            </td>
                            @error('details.'.$i.'.quantity') <p class="small text-danger">{{ $message }}</p> @enderror
                            <td>
                                @if (!empty($item->is_disbursed))
                                    <span class="badge bg-success">Disbursed</span>
                                @else
                                    <span class="badge bg-warning">Yet To Disburse</span>
                                @endif
                                <span></span>
                            </td>
                            <td>
                                @if (!empty($is_stock))
                                    {{ $maxStock }} ctns ( {{$pieces}} pcs )
                                @else
                                    {{$stockStatus}}
                                @endif 
                            </td>
                            <td>
                                <a  onclick="removeItem({{$i}},{{$item->product_id}},{{ $item->quantity }},{{$pcs}});" class="btn btn-outline-danger select-md">Remove Item</a>
                            </td>
                        </tr>
                        @php
                            $i++;
                        @endphp
                        @empty
                            
                        @endforelse
                        
                    </tbody>
                    <tbody>
                        <tr class="table-info"> 
                            <td>Total</td>
                            <td></td>
                            <td>
                                <span id="totalReq">{{$totalCtns}} ({{$totalPcs}} pcs)</span>
                            </td>
                            <td>
                                <span id="totalDisburse"></span>
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <p>** Please remember, Your Disbursed Carton will be set as final order product carton quantity</p>
                    <a href="{{ route('admin.packingslip.index') }}" class="btn btn-sm btn-danger">Back</a>
                    <input type="submit" id="submitBtn" class="btn btn-sm btn-success" value="Update" />
                </div>
            </div>
            </form>
        </div>
    </div>
    
</section>
@endsection

@section('script')
    <script>
        
        $(document).ready(function(){
            $('div.alert').delay(3000).slideUp(300);

            $("#myForm").submit(function() {
                // $('input').attr('disabled', 'disabled');
                $('#submitBtn').attr('disabled', 'disabled');
                return true;
            });
        })

        function getQuantity(i){
            // alert(i)
            var oldQty = $('#oldQty'+i).val();
            var maxstock = $('#maxstock'+i).val();
            var propcs = $('#propcs'+i).val();
            var quantity = $('#quantity'+i).val();
            var pcs = (quantity * propcs);
            $('#pcs'+i).val(pcs);
            if(quantity != oldQty){
                $('#isChanged'+i).val(1);
            } else {
                $('#isChanged'+i).val(0);
            }
            // isChanged
        }
        function removeItem(i,e,oldQty,pcs){
            var count_tr_pro = $('.tr_pro').length;   
            if(count_tr_pro > 1){
                var isRemove = confirm("Are you sure want to remove item?");
                // alert('index:- '+i+' & product_id:- '+e+' & required qty:- '+oldQty+' & total_pcs:- '+pcs);
                if(isRemove == true){
                    alert('Removed');
                    $('#row'+i).remove();
                    var totalCtns = 0;
                    $('.oldQty').each(function(){
                        totalCtns += parseInt($(this).val());
                    });

                    var totalPcs = 0;
                    $('.oldPcs').each(function(){
                        totalPcs += parseInt($(this).val());
                    });
                    // alert('totalCtns:- '+totalCtns+' & totalPcs:- '+totalPcs);
                    $('#totalReq').html(totalCtns+' ('+totalPcs+' pcs)')
                } else {
                    alert('Not Removed');
                }
            }  
            
        }
        
    </script>
@endsection
