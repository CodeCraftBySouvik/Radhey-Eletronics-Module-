@extends('admin.layouts.app')
@section('page', 'Edit Partner Expense')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Accounting</li>
        <li>Edit Partner Expense</li>
    </ul>
    <div class="row">
        <div class="col-md-12">
            {{-- @if($errors->any())                     
                {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
            @endif --}}
            @if (Session::has('message'))
            <div class="alert alert-success" role="alert">
                {{ Session::get('message') }}
            </div>
            @endif
            
            <form id="myForm" action="{{ route('admin.accounting.update_partner_expense',$id) }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="">Voucher No</label>
                            <input type="text" value="{{$payment->voucher_no}}" name="voucher_no" readonly id="voucher_no" class="form-control">
                        </div>
                    </div>  
                    
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="">Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ $payment->payment_date }}"  autocomplete="off" max="{{ date('Y-m-d')}}">
                            @error('payment_date') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="">Amount <span class="text-danger">*</span></label>
                            
                            <input type="text" id="amount" value="@if(old('amount')){{old('amount')}}@else{{ $payment->amount }}@endif" pattern="^\d*(\.\d{0,2})?$"  maxlength="20" name="amount" class="form-control" onkeypress='validateNum(event)'>
                            @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group mb-3">
                            <label for="">Mode of Payment <span class="text-danger">*</span></label>
                            @if (old('payment_mode'))
                            <select name="payment_mode" class="form-control" id="payment_mode">
                                <option value="">Select One</option>
                                <option value="cheque" @if(old('payment_mode') == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if(old('payment_mode') == 'neft') selected @endif>NEFT</option>
                                <option value="cash" @if(old('payment_mode') == 'cash') selected @endif>Cash</option>
                            </select>
                            @else
                            <select name="payment_mode" class="form-control" id="payment_mode">
                                <option value="">Select One</option>
                                <option value="cheque" @if($payment->payment_mode == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if($payment->payment_mode == 'neft') selected @endif>NEFT</option>
                                <option value="cash" @if($payment->payment_mode == 'cash') selected @endif>Cash</option>
                            </select>
                            @endif
                            
                            <input type="hidden" name="payment_in" id="payment_in" value="@if(old('payment_in')){{old('payment_in')}}@else{{$payment->payment_in}}@endif">
                            <input type="hidden" name="bank_cash" id="bank_cash" value="@if(old('bank_cash')){{old('bank_cash')}}@else{{$payment->bank_cash}}@endif">
                            @error('payment_mode') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>    
                <div class="row" id="noncash_sec"> 
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Cheque No / UTR No <span class="text-danger">*</span></label>
                            <input type="text" value="@if(old('chq_utr_no')){{old('payment_in')}}@else{{ $payment->chq_utr_no }}@endif" name="chq_utr_no" class="form-control" maxlength="100">
                            @error('chq_utr_no') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Bank Name <span class="text-danger">*</span></label>
                            <div id="bank_search">
                                <input type="text" id="" placeholder="Search Bank" name="bank_name" value="@if(old('bank_name')){{old('bank_name')}}@else{{ $payment->bank_name }}@endif" onkeyup="getBankList(this.value);" class="form-control bank_name" maxlength="200">
                                <input type="hidden" class="form-control"  name="bank_name_hidden" value="@if(old('bank_name_hidden')){{old('bank_name_hidden')}}@else{{ $payment->bank_name }}@endif"  id="bank_name">
                                @error('bank_name') <p class="small text-danger">{{ $message }}</p> @enderror
                                <div class="resBankProp"></div>
                            </div>
                            <div id="bank_custom" style="display: none;">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="bank_name">
                                    <div class="input-group-append">
                                      <a class="btn btn-outline-secondary" id="allbankothers"><i class="fa fa-refresh"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                    </div>  
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="">Narration</label>
                            <textarea name="narration" class="form-control" style="width: 50%;  height: 100px;" id="" cols="10" rows="10">{{ $payment->narration }}</textarea>
                        </div>
                    </div>                    
                </div>                
                <div class="row">
                    <div class="form-group">
                        <button id="submitBtn" type="submit" class="btn btn-sm btn-success">Update</button>
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
    
    $( document ).ready(function() {
        $('div.alert').delay(3000).slideUp(300);

        @if (old('bank_cash'))
        var bank_cash = "{{old('bank_cash')}}";
        if(bank_cash == 'bank'){
            $('#noncash_sec').show();
        } else {
            $('#noncash_sec').hide();
        }
        @else
        var bank_cash = $('#bank_cash').val();
        if(bank_cash == 'bank'){
            $('#noncash_sec').show();
        } else {
            $('#noncash_sec').hide();
        }
        @endif
        
        
        $("#myForm").submit(function() {
            // $('input').attr('disabled', 'disabled');
            $('#submitBtn').attr('disabled', 'disabled');
            return true;
        });
    });
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
    $('#payment_mode').on('change', function(){
        console.log(this.value);
        if(this.value == 'cash'){
            $('#noncash_sec').hide();
            $('#bank_cash').val('cash');
            $('#payment_in').val('cash');
        }else{
            $('#noncash_sec').show();
            $('#bank_cash').val('bank');
            $('#payment_in').val('bank');
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

    $('#allbankothers').on('click', function(){
        
        $('#bank_custom').hide();
        $('#bank_search').show();
    });

   
    
</script>
@endsection
