@extends('admin.layouts.app')
@section('page', 'Add Partner Expense')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Accounting</li>
        <li>Add Partner Expense</li>
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
            
            <form id="myForm" action="{{route('admin.accounting.save_partner_expense')}}" method="POST">
                @csrf
                <input type="hidden" name="admin_id" value="{{ Auth::user()->id }}">
                
                @php
                    $voucher_no = "PTREXP".time();                                       
                @endphp
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Voucher No</label>
                            <input type="text" value="@if (!empty(old('voucher_no'))) {{old('voucher_no')}} @else {{$voucher_no}} @endif" name="voucher_no" readonly id="voucher_no" class="form-control">
                        </div>
                    </div>                    
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Amount <span class="text-danger">*</span></label>
                            
                            <input type="text" id="amount" value="{{ old('amount') }}" pattern="^\d*(\.\d{0,2})?$"  maxlength="20" name="amount" class="form-control" onkeypress='validateNum(event)'>
                            @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" max="{{ date('Y-m-d')}}" value="{{ old('payment_date') }}" class="form-control" id="">
                            @error('payment_date') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Mode of Payment <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-control" id="payment_mode">
                                <option value="">Select One</option>
                                <option value="cheque" @if(old('payment_mode') == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if(old('payment_mode') == 'neft') selected @endif>NEFT</option>
                                <option value="cash" @if(old('payment_mode') == 'cash') selected @endif>Cash</option>
                            </select>
                            @error('payment_mode') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Expense <span class="text-danger">*</span></label>
                            <select name="expense_id" class="form-control" id="">
                                <option value="">Select an option</option>
                                @forelse ($expense_types as $type)
                                    <option value="{{$type->id}}" @if(old('expense_id') == $type->id) selected @endif>{{$type->title}}</option>
                                @empty
                                    <option value=""> - No option found - </option>
                                @endforelse
                            </select>
                            @error('expense_id') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>                    
                </div>
                
                <div class="row" id="noncash_sec"> 
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Cheque No / UTR No </label>
                            <input type="text" value="{{ old('chq_utr_no') }}" name="chq_utr_no" class="form-control" maxlength="100">
                            @error('chq_utr_no') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Bank Name </label>
                            <div id="bank_search">
                                <input type="text" id="" placeholder="Search Bank" name="bank_name" value="{{ old('bank_name') }}" onkeyup="getBankList(this.value);" class="form-control bank_name" maxlength="200">
                                <input type="hidden" class="form-control"  name="bank_name_hidden" value="{{ old('bank_name') }}"  id="bank_name">
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
                            <textarea name="narration" class="form-control" style="width: 50%;  height: 100px;" id="" cols="10" rows="10">{{ old('narration') }}</textarea>
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
    
    $( document ).ready(function() {
        $('div.alert').delay(3000).slideUp(300);
        
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


    function fetchCode(id,name) {
        $('.user-dropdown').hide()
        $('#select_user').val(id)
        // $('#user').val(id)
        $('#select_user_name').val(name) 
        $('#user_id').val(id)
    }

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

    $('#allbankothers').on('click', function(){
        
        $('#bank_custom').hide();
        $('#bank_search').show();
    });

   
    
</script>
@endsection
