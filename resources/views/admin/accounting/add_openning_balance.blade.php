@extends('admin.layouts.app')
@php
    if(empty($type)){
        $title = "Customer";
    }else{
        $title = ucwords($type);
    }
@endphp
@section('page', 'Add Opening Balance '.ucwords($title))
@section('content')
<section>
    
    <ul class="breadcrumb_menu">
        <li>Accounting</li>
        <li>Add Opening Balance {{ ucwords($title)}}</li>
    </ul>
    <div class="row">
        <div class="col-md-12">
            {{-- @if($errors->any())                     
                {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
            @endif             --}}
            @if (Session::has('message'))
            <div class="alert alert-success" role="alert">
                {{ Session::get('message') }}
            </div>
            @endif
            @if(!empty($type))
            <form id="myForm" action="{{ route('admin.accounting.save_opening_balance_partner') }}" method="POST">
            @else
            <form id="myForm" action="{{ route('admin.accounting.save_opening_balance') }}" method="POST">
            @endif
            
                @csrf    
                <input type="hidden" name="type_val" id="type_val" value="{{$type}}">            
                <div class="row">   
                    @if(empty($type))                 
                    <div class="col-sm-3">                        
                        <div class="form-group mb-3">
                            <label for="" id="lable_user">Customer <span class="text-danger">*</span></label>
                            <input type="hidden" name="store_id" value="{{ old('store_id') }}">
                            <input type="text" name="store_name" placeholder="Search Customer" class="form-control" value="{{ old('store_name') }}" id="store_name">                            
                            @error('store_id') <p class="small text-danger">{{ $message }}</p> @enderror

                            <div class="respDrop"></div>
                        </div>
                    </div>
                    @else
                    <div class="col-sm-3">                        
                        <div class="form-group mb-3">
                            <label for="" id="lable_user">{{ucwords($type)}} <span class="text-danger">*</span></label>
                            <input type="hidden" name="admin_id" value="{{ old('admin_id') }}">
                            <input type="text" name="admin_name" placeholder="Search Partner" class="form-control" value="{{ old('admin_name') }}" id="store_name">                            
                            @error('admin_id') <p class="small text-danger">{{ $message }}</p> @enderror

                            <div class="respDrop"></div>
                        </div>
                    </div>                   
                    @endif
                    {{-- <input type="hidden" name="payment_for" value="credit"> --}}
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="">Credit / Debit <span class="text-danger">*</span></label>
                            <select name="payment_for" class="form-control" id="payment_for">
                                <option value="" selected hidden>Select an option</option>
                                <option value="credit" @if(old('payment_for') == 'credit') selected @endif>Credit</option>
                                <option value="debit" @if(old('payment_for') == 'debit') selected @endif >Debit</option>                                 
                            </select>
                            @error('payment_for') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="">Bank / Cash / Bank + Cash <span class="text-danger">*</span></label>
                            <select name="payment_in" class="form-control" id="payment_in" onchange="changePaymentIn(this.value);" >
                                <option value="" selected hidden>Select One</option>
                                <option value="bank" @if(old('payment_in') == 'bank') selected @endif>Bank</option>
                                <option value="cash" @if(old('payment_in') == 'cash') selected @endif>Cash</option>
                                <option value="bank_n_cash" @if(old('payment_in') == 'bank_n_cash') selected @endif>Bank + Cash</option>
                            </select>
                            @error('payment_in') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="">Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date') }}"  autocomplete="off" max="{{ date('Y-m-d')}}">
                            @error('payment_date') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                    
                </div>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="">Voucher No</label>
                            <input type="text" value="@if (!empty(old('voucher_no'))) {{ old('voucher_no') }} @else {{ 'BALANCE'.time() }} @endif" name="voucher_no" readonly class="form-control">
                        </div>
                    </div>
                    <div class="col-sm-4 bank_n_cash_div">
                        <div class="form-group mb-3">
                            <label for="">Bank Amount <span class="text-danger">*</span></label>
                            <input type="text" maxlength="20" pattern="^\d*(\.\d{0,2})?$"  value="{{ old('bank_amount') }}"  name="bank_amount" class="form-control" >
                            @error('bank_amount') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                    <div class="col-sm-4 bank_n_cash_div">
                        <div class="form-group mb-3">
                            <label for="">Cash Amount <span class="text-danger">*</span></label>
                            <input type="text" maxlength="20" pattern="^\d*(\.\d{0,2})?$"  value="{{ old('cash_amount') }}"  name="cash_amount" class="form-control" >
                            @error('cash_amount') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>                            
                    </div> 
                    <div class="col-sm-4 bank_or_cash_div">                        
                        <div class="form-group mb-3">
                            <label for="">Amount <span class="text-danger">*</span></label>
                            <input type="text" maxlength="20" pattern="^\d*(\.\d{0,2})?$"  value="{{ old('amount') }}"  name="amount" class="form-control" >
                            @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>                        
                    </div> 
                                      
                </div>
                <div class="row" id="noncash_sec">  
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Bank Name </label>
                            <div id="bank_search">
                                <input type="text" id="" placeholder="Search Bank" name="bank_name" value="{{ old('bank_name') }}" onkeyup="getBankList(this.value);" class="form-control bank_name" maxlength="200">
                                <input type="hidden" class="form-control"  name="bank_name_hidden" value="{{ old('bank_name') }}"  id="bank_name">
                                @error('bank_name') <p class="small text-danger">{{ $message }}</p> @enderror
                                <div class="resBankProp"></div>
                            </div>
                            <div id="bank_custom">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="bank_name">
                                    <div class="input-group-append">
                                      <a class="btn btn-outline-secondary" id="allbankothers" title="Click to get all banks"><i class="fa fa-refresh"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4" id="cheque_neft_div">
                        <div class="form-group mb-3">
                            <label for="">Cheque / NEFT <span class="text-danger">*</span></label>
                            <select name="cheque_neft" class="form-control" id="cheque_neft">
                                <option value="" hidden selected>Select One</option>
                                <option value="cheque" @if(old('cheque_neft') == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if(old('cheque_neft') == 'neft') selected @endif>NEFT</option>                                
                            </select>
                            
                            @error('payment_mode') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <input type="hidden" name="payment_mode" id="payment_mode" value="{{ old('payment_mode') }}">                  
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Cheque No / UTR No </label>
                            <input type="text" value="{{ old('chq_utr_no') }}" name="chq_utr_no" class="form-control" maxlength="100">
                            @error('chq_utr_no') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Narration </label>
                            <textarea name="narration" id="" style="width: 100%;  height: 100px;" cols="10" rows="10" class="form-control">{{ old('narration') }}</textarea>
                            @error('narration') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>  
                    </div>
                </div>
                         
                <div class="row">
                    <div class="form-group">
                        <button id="submitBtn" type="submit" class="btn btn-sm btn-success">Add</button>
                        
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<script>

    $(document).on('keydown', 'input[pattern]', function(e){
        var input = $(this);
        var oldVal = input.val();
        var regex = new RegExp(input.attr('pattern'), 'g');

        setTimeout(function(){
            var newVal = input.val();
            if(!regex.test(newVal)){
            input.val(oldVal); 
            }
        }, 1);
    });
    
    $('#store_name').on('keyup', function(){
        var ajaxUrl = "{{ route('admin.ledger.getUsersByType', ['type'=> 'store' ]) }}";
        var typeVal = 'store';
        var type_val = $('#type_val').val();
        if(type_val == 'partner'){
            ajaxUrl = "{{ route('admin.ledger.getUsersByType', ['type'=> 'partner' ]) }}";
            typeVal = 'partner';
        }   
        
        console.log(ajaxUrl)

        if(this.value.length > 0){
            console.log(this.value);
            $.ajax({
                url: ajaxUrl,
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: this.value,
                },
                success: function(result) {

                    console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {
                            if(typeVal == 'store'){
                                if(value.bussiness_name != ''){
                                    content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.bussiness_name}','${typeVal}')">${value.bussiness_name}</a>`;
                                } else {
                                    content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}','${typeVal}')">${value.name}</a>`;
                                }
                            } else {
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}','${typeVal}')">${value.name}</a>`;
                            }
                            
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No store found</li></div>`;
                    }
                    $('.respDrop').html(content);
                }
            });
        }else{
            $('.respDrop').text('');
            $('#store_id').val(0);
        }
    });

    function fetchCode(id,name,type) {
        $('.user-dropdown').hide()
        // alert(type);
        if(type == 'store'){
            $('input[name="store_id"]').val(id)
            $('input[name="store_name"]').val(name)
        }else if(type == 'partner'){
            $('input[name="admin_id"]').val(id)
            $('input[name="admin_name"]').val(name)
        }
        
    }

    $(document).ready( function(){
        $('div.alert').delay(3000).slideUp(300);
        $('#bank_custom').hide();
        $('.bank_n_cash_div').hide();
        var payment_in = $('#payment_in').val();
        changePaymentIn(payment_in)

        $('#cheque_neft').on('change', function(){
            $('#payment_mode').val(this.value);
        })

    })

    function changePaymentIn(e)
    {
        console.log(e)
        if(e != ''){
            if(e == 'cash'){
                $('#noncash_sec').hide();
                $('#cheque_neft_div').hide();
                $('#payment_mode').val(e);
                $('.bank_n_cash_div').hide();
                $('.bank_or_cash_div').show();
            } else if(e == 'bank_n_cash') {
                $('.bank_n_cash_div').show();
                $('.bank_or_cash_div').hide();
                $('#noncash_sec').show();
                $('#cheque_neft_div').show();
                $('#payment_mode').val($('#cheque_neft').val());
            }
            else{
                $('#noncash_sec').show();
                $('#cheque_neft_div').show();
                $('#payment_mode').val($('#cheque_neft').val());
                $('.bank_n_cash_div').hide();
                $('.bank_or_cash_div').show();
            }
        }else{
            $('#payment_mode').val('');
        }
    }

   
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
            $('#bank_name').val('');
            
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
    
    $('#allbankothers').on('click', function(){
        
        $('#bank_custom').hide();
        $('#bank_search').show();
    });

    $("#myForm").submit(function() {
        $('#submitBtn').attr('disabled', 'disabled');
        return true;
    });


</script>
@endsection
