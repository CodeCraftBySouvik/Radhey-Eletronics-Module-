@extends('admin.layouts.app')
@section('page', 'Add Payment Receipt')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li><a href="{{ route('admin.paymentcollection.index') }}">Payment Collection</a></li>
        <li>Add Payment Receipt</li>
    </ul>
    <div class="row">
        @php
            $readonly = "readonly";
            if(empty($payment_collection)){
                $readonly = "";
            }
        @endphp
        <div class="col-md-12">
            {{-- @if($errors->any())                     
                {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
            @endif             --}}
            @if (Session::has('message'))
            <div class="alert alert-success" role="alert">
                {{ Session::get('message') }}
            </div>
            @endif
            <form id="payment_form" action="{{ route('admin.accounting.save_payment_receipt') }}" method="POST">
                @csrf   
                <input type="hidden" name="payment_collection_id" value="{{$paymentCollectionId}}">             
                <div class="row">                    
                    <div class="col-sm-4">                        
                        <div class="form-group mb-3">
                            <label for="" id="">Store <span class="text-danger">*</span></label>
                            @if (!empty($payment_collection))
                            <input type="hidden" name="store_id" value="{{ $payment_collection->store_id }}">
                            <input type="text" name="" value="{{ $payment_collection->store_name }}" class="form-control"  id="store_name" {{$readonly}}>
                            @else
                            
                            <input type="text" name="store_name" class="form-control" id="store_name" placeholder="Search store by name" onkeyup="getStores(this.value);" value="{{ old('store_name') }}">
                            <input type="hidden" name="store_id" id="store_id" value="{{ old('store_id') }}">
                            <div class="respDropStore" id="respDropStore" style="position: relative;"></div>
                                    
                            
                                
                            @endif
                            @error('store_id') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>  
                   
                    <div class="col-sm-4">                        
                        <div class="form-group mb-3">
                            <label for="" id="">Collected By <span class="text-danger">*</span></label>
                            @if (!empty($payment_collection))
                            <input type="hidden" name="staff_id" value="{{ $payment_collection->user_id }}">
                            <input type="text" name="" value="{{ $payment_collection->staff_name }}" class="form-control"  id="" {{$readonly}}>  
                            @else
                            <select name="staff_id" class="form-control" id="">
                                <option value="">Choose an user</option>
                                @forelse ($users as $user)
                                    <option value="{{$user->id}}" @if(old('staff_id') == $user->id) selected @endif>{{$user->name}}</option>
                                @empty
                                    
                                @endforelse
                            </select>
                            @error('staff_id') <p class="small text-danger">{{ $message }}</p> @enderror
                            
                            @endif
                            
                        </div>
                    </div>    
                    
                                        
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Amount <span class="text-danger">*</span></label>
                            @if (!empty($payment_collection))
                            <input type="text" value="{{ $payment_collection->collection_amount }}" maxlength="20" name="amount" {{$readonly}} class="form-control">
                            @else
                            <input type="text" value="{{ old('amount') }}" maxlength="20" name="amount" class="form-control">
                            @endif
                            
                            @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Voucher No</label>
                            <input type="text" value="@if (!empty(old('voucher_no'))) {{old('voucher_no')}} @else {{'PAYRECEIPT'.time()}} @endif" name="voucher_no" {{$readonly}} class="form-control">
                        </div>
                    </div>
                    @php                        
                        // $paymentDate = !empty($payment_collection)?$payment_collection->cheque_date:'';
                    @endphp
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Date <span class="text-danger">*</span></label>
                            @if (!empty($payment_collection))
                            <input type="date" name="payment_date" id="payment_date" max="{{date('Y-m-d')}}" class="form-control" value="{{ $payment_collection->cheque_date }}"  {{$readonly}}>
                            @else
                            <input type="date" name="payment_date" id="payment_date" max="{{date('Y-m-d')}}" class="form-control" value="{{ old('payment_date') }}"  {{$readonly}}>
                            @endif
                            
                            @error('payment_date') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                    <div class="col-sm-4">
                        @if (!empty($payment_collection))
                        <input type="hidden" name="payment_mode" value="{{ $payment_collection->payment_type }}">
                        @endif
                        
                        <div class="form-group mb-3">
                            <label for="">Mode of Payment <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-control" @if(!empty($payment_collection)) disabled @endif  id="payment_mode">
                                <option value="" selected hidden>Select One</option>
                                @if (!empty($payment_collection))
                                <option value="cheque" @if($payment_collection->payment_type == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if($payment_collection->payment_type == 'neft') selected @endif>NEFT</option>
                                @if (Auth::user()->designation != 3)
                                <option value="cash" @if($payment_collection->payment_type == 'cash') selected @endif>Cash</option>
                                @endif
                                
                                @else
                                <option value="cheque" @if(old('payment_mode') == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if(old('payment_mode') == 'neft') selected @endif>NEFT</option>
                                @if (Auth::user()->designation != 3)
                                <option value="cash" @if(old('payment_mode') == 'cash') selected @endif>Cash</option>  
                                @endif
                                @endif
                                
                            </select>
                            @error('payment_mode') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                <div class="row" id="noncash_sec">                    
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Cheque No / UTR No </label>
                            @if (!empty($payment_collection))
                            <input type="text" {{$readonly}} value="{{ $payment_collection->cheque_number }}" name="chq_utr_no" class="form-control" maxlength="100">
                            @else
                            <input type="text" {{$readonly}} value="{{ old('chq_utr_no') }}" name="chq_utr_no" class="form-control" maxlength="100">
                            @endif
                            
                            @error('chq_utr_no') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Bank Name </label>
                            <div id="bank_search">
                                @if (!empty($payment_collection))
                                <input type="text" id="" value="{{ $payment_collection->bank_name }}" name="bank_name" {{$readonly}} class="form-control" maxlength="200">
                                
                                @else
                                <input type="text" id="" placeholder="Search Bank" name="bank_name" value="{{ old('bank_name') }}" onkeyup="getBankList(this.value);" class="form-control bank_name" maxlength="200">
                                <input type="hidden" class="form-control"  name="bank_name_hidden" value="{{ old('bank_name') }}"  id="bank_name">
                                <div class="resBankProp"></div>
                                @endif
                                
                            </div>                                                      
                        </div>
                    </div>                    
                </div>
                         
                <div class="row">
                    <div class="form-group">
                        <a href="{{ route('admin.paymentcollection.index') }}" class="btn btn-sm btn-danger">Back</a>
                        <button type="submit" id="submit_btn" class="btn btn-sm btn-success">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<script>

    $(document).ready(function(){
        
        var paymentCollectionId = "{{$paymentCollectionId}}";
        // alert(paymentCollectionId);
        if(paymentCollectionId == 0){            
            var payment_mode = "{{ old('payment_mode') }}";
            console.log(payment_mode);
            if(payment_mode == ""){                
                $('#noncash_sec').hide();
            }else {
                if(payment_mode == 'cash'){
                    $('#noncash_sec').hide();
                } else {
                    $('#noncash_sec').show();
                }
            }
        }else{
            // alert(paymentCollectionId);
            $('#noncash_sec').show();
        }
        
        
    })
    
    $("#payment_form").submit(function() {
        // $('input').attr('disabled', 'disabled');
        $('#submit_btn').attr('disabled', 'disabled');
        return true;
    });

    $('#payment_mode').on('change', function(){
        console.log(this.value);
        if(this.value == 'cash'){
            $('#noncash_sec').hide();
        }else{
            $('#noncash_sec').show();
        }
    });

    function getBankList(evt)
    {
        
        if(evt.length > 0){
            // console.log(evt);
            $.ajax({
                url: "{{ route('admin.ledger.getBankList') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: evt
                },
                success: function(result) {

                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 bankname-dropdown" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchBankName('${value.name}')">${value.name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 bankname-dropdown" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No bank found</li></div>`;
                    }
                    $('.resBankProp').html(content);
                }
            });
        }else{
            $('.resBankProp').text('');
            
        }
    }

    function fetchBankName(name)
    {
        if(name != ' - OTHERS -'){
            $('.bankname-dropdown').hide();           
            $('input[name="bank_name"]').val(name)
            $('input[name="bank_name_hidden"').val(name)
        }else{
            $('#bank_search').hide();
            $('#bank_custom').show();
        }
        
    }  

    function getStores(val){
        if(val.length > 0){
            $.ajax({
                url: "{{ route('admin.ledger.getUsersByType') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: val,
                    type: 'store'
                },
                success: function(result) {

                    console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {                        
                            if(value.bussiness_name != ''){
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.bussiness_name}')">${value.bussiness_name}</a>`;
                            } else {
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}')">${value.name}</a>`;
                            }                        
                            
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No store found</li></div>`;
                    }
                    $('.respDropStore').html(content);
                }
            });
        } else {
            $('.respDropStore').text('');
            $('#store_id').val(0);
            $('#store_name').val('');
        }
        
    }

    function fetchCode(id,name) {
        $('.user-dropdown').hide()
        $('input[name="store_id"]').val(id)
        $('input[name="store_name"]').val(name)
        
        
    }

</script>
@endsection
