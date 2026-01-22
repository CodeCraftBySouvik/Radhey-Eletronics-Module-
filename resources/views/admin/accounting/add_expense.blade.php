@extends('admin.layouts.app')
@section('page', 'Add Depot Expense')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Accounting</li>
        <li>Add Depot Expense</li>
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
            
            <form id="myForm" action="{{ route('admin.accounting.save_expenses') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Expense At <span class="text-danger">*</span></label>
                            <select name="user_type" id="user_type" class="form-control" onchange="getUserTypes(this.value);">
                                <option value="" selected hidden>Select an option</option>
                                <option value="staff" @if(old('user_type') == 'staff') selected @endif>Staff</option>
                                <option value="store" @if(old('user_type') == 'store') selected @endif>Store</option>
                                <option value="supplier" @if(old('user_type') == 'supplier') selected @endif>Supplier</option>
                                <option value="miscellaneous" @if(old('user_type') == 'miscellaneous') selected @endif>Miscellaneous</option>
                            </select>
                            @error('user_type') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ old('payment_date') }}"  autocomplete="off" max="{{ date('Y-m-d')}}">
                            @error('payment_date') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                </div>
                <div class="row" id="expense_div">
                    <div class="col-sm-6">
                        {{-- <input type="hidden" name="user" id="user" value=""> --}}
                        <div class="form-group mb-3">
                            <label for="" id="lable_user">Staff / Store / Partner </label> <span class="text-danger">*</span>
                            
                            <input type="hidden" name="" id="select_user" value="">
                            <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id') }}">
                            <input type="text" name="user_name" value="{{ old('user_name') }}" placeholder="" class="form-control"  id="select_user_name" onkeyup="getUsers(this.value);">                            
                            @error('user_name') <p class="small text-danger ">{{ $message }}</p> @enderror
                            @error('user_id') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="respDrop"></div>
                    </div>                    
                    <div class="col-sm-6">
                        <input type="hidden" name="expense_name" id="expense_name">
                        <div class="form-group mb-3" id="expense_div">
                            <label for="">Expense </label> <span class="text-danger">*</span>
                            <select name="expense_id" class="form-control" id="expense_id" >
                                <option value="">Select an option</option>
                                
                            </select>
                            @error('expense_id') <p class="small text-danger expense_id">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                @php
                    $voucher_no = "EXPENSE".time();                                       
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
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Narration</label>
                            <textarea name="narration" class="form-control" style="height: 100px;" id="" cols="10" rows="10">{{ old('narration') }}</textarea>
                        </div>
                    </div>                    
                </div>
                
                <div class="row">
                    <div class="form-group">
                        <button id="submitBtn" type="submit" class="btn btn-sm btn-success">Add</button>
                        <a href="{{ route('admin.accounting.list_expenses') }}" class="btn btn-outline-danger">List Depot Expenses</a>
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
        var user_type = $('#user_type').val();
        var select_user_name = $('#select_user_name').val();

        $('#expense_div').hide();

        console.log(user_type);
        getUserTypes(user_type);
        // getUsers(select_user_name);
        var old_user_name = "{{ old('user_name') }}";
        var old_user_id = "{{ old('user_id') }}";
        $('#select_user_name').val(old_user_name);
        $('#select_user').val(old_user_id);
        $('#user_id').val(old_user_id);
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

    function getUserTypes(evt)
    {
        // alert(evt);
        if(evt != ''){
            $('#select_user_name').val('');
            $('#select_user').val(0);
            $('#user_id').val(0);
            if(evt != 'miscellaneous'){
                $('#expense_div').show();
            }
            if(evt == 'staff'){
                $('#select_user').attr("name", "staff_id");
                $('#lable_user').html('Staff');                
            }else if(evt == 'store'){
                $('#select_user').attr("name", "store_id");
                $('#lable_user').html('Store');
            }else if(evt == 'supplier'){
                $('#select_user').attr("name", "supplier_id");
                $('#lable_user').html('Supplier');
            }

            if(evt != 'miscellaneous'){
                var old_expense_id = "{{ old('expense_id') }}";
                // alert(old_expense_id)
                $.ajax({
                    url: "{{ route('admin.ledger.getRequiredExpenses') }}",
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        'user_type': evt,
                        "_token": "{{ csrf_token() }}"
                    },
                    success: function( data){
                        console.log(data);
                        var expenseData = ``;
                        expenseData = `<option value="0">Select an expense</option>`;
                        for(var i =0; i < data.length; i++){
                            var expenseSelected = "";
                            if(old_expense_id == data[i].id){
                                expenseSelected = "selected";
                            }
                            expenseData += `<option value="`+data[i].id+`" data-expense-name="`+data[i].title+`" `+expenseSelected+` >`+data[i].title+`  </option>`;
                        }
                        
                        $('#expense_id').html(expenseData);
                        
                    }
                    
                });
            }else{
                $('#expense_div').hide();
            }

            

        }else{
            $('#select_user_name').val('');
            $('#select_user').val(0);
            $('#user_id').val(0);
            $('#lable_user').html('Staff / Store / Partner / Supplier');
            // $('<span class="text-danger">*</span>').insertAfter('#lable_user');
        }
        
    }

    $('#expense_id').on('change', function(){
        var e = this.value;        
        var expense_name = $('option:selected', this).attr('data-expense-name'); 
        // alert(expense_name);
        $('#expense_name').val(expense_name);
        

    });

    function getUsers(evt){
        var user_type = $('#user_type').val();
        if(evt.length > 0){
            console.log(evt);
            $.ajax({
                url: "{{ route('admin.ledger.getUsersByType') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: evt,
                    type: user_type
                },
                success: function(result) {

                    console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {
                            if(user_type == 'store'){
                                if(value.bussiness_name != ''){
                                    content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.bussiness_name}')">${value.bussiness_name}</a>`;
                                } else {
                                    content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}')">${value.name}</a>`;
                                }
                            } else {
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}')">${value.name}</a>`;
                            }

                        })
                        content += `</div>`;
                    } else {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No user found</li></div>`;
                    }
                    $('.respDrop').html(content);
                }
            });
        }else{
            $('.respDrop').text('');
            $('#select_user').val(0);
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
