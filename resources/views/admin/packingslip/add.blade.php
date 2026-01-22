@extends('admin.layouts.app')
@section('page', 'Add Packing Slip')
@section('content')
<section>
    <h4>Order No:- <span>#{{$order->order_no}}</span></h4>
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
            <form id="myForm" action="{{ route('admin.packingslip.save') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{$order_id}}">
            <input type="hidden" name="store_id" value="{{$order->store_id}}">
            @if($errors->any())            
                {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
            @endif
            @php
                $submit_disabled = "disabled";
            @endphp
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Required Quantity</th>
                            <th>Disburse Quantity</th>
                            <th>Total Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i=1;
                            $totalCtns = 0;
                            $totalPcs = 0;
                        @endphp
                        @forelse ($order_products as $op)
                            
                        @php
                            $checkStockPO = checkStockPO($op->product_id,$op->qty);
                            $stockStatus = $checkStockPO['status'];
                            $maxStock = $checkStockPO['stock'];
                            $is_stock = $checkStockPO['is_stock'];
                            $pieces = $checkStockPO['pieces'];
                            
                            $disabled = "";

                            $getOrderProductDetails = getOrderProductDetails($order_id,$op->product_id);
                            $pcs = $getOrderProductDetails->pcs;
                            $totalCtns += $op->qty;
                            $totalPcs += $pcs;

                            if(!empty($no_of_ctns)){
                                $submit_disabled = "";
                            }
                            
                            $required_qty = ($op->qty - $op->release_qty);
                            
                            if($is_stock == 0){
                                $disabled = "disabled";
                            }
                            if($op->qty == $op->release_qty){
                                $disabled = "disabled";
                            }

                            if($maxStock >= $required_qty){
                                $max = $required_qty;
                            }else{
                                $max = $maxStock;
                            }

                        @endphp
                        
                        <tr>   
                            <td>{{$i}}</td>
                            <input type="hidden" name="details[{{$i}}][pcs]" value="{{$pcs}}">                     
                            <td><p class="m-0"> {{$op->pro_name}} </p> </td>
                            <td><p class="m-0"> {{$required_qty}} ctns ( {{$required_qty * $op->pcs}} pcs ) </p> </td>
                            <td>
                                @if (!empty($maxStock))
                                    <input type="number" id="qty{{$op->product_id}}" {{$disabled}} class="form-control" @if($disabled == "") value="{{$required_qty}}" @endif min="1" max="{{ $max }}"  name="details[{{$i}}][quantity]" >
                                    <input type="hidden" name="details[{{$i}}][product_id]" value="{{$op->product_id}}"> 
                                    @error('details.'.$i.'.quantity') <p class="small text-danger">{{ $message }}</p> @enderror
                                @else
                                    <input type="text" name="" class="form-control" disabled id="" placeholder="OUT OF STOCK">
                                @endif
                            </td>                        
                            <td>
                                @if (!empty($is_stock) && !empty($required_qty))
                                    {{ $maxStock }} ctns ( {{$pieces}} pcs )
                                @else
                                    {{$stockStatus}}
                                @endif                             
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
                            <td>{{$totalCtns}} ({{$totalPcs}} pcs)</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card shadow-sm">
                <div class="card-body">
                    <a href="{{ route('admin.order.index',['status'=>$order->status]) }}" class="btn btn-sm btn-danger">Back</a>
                    <input type="submit" id="submitBtn"  class="btn btn-sm btn-success" value="Save" />
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
    </script>
@endsection
