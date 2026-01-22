@extends('admin.layouts.app')
@section('page', 'Add Bad Debt')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Accounting</li>
        <li>Add Bad Debt</li>
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
            <form id="payment_form" action="{{ route('admin.accounting.save_bad_debt') }}" method="POST">
                @csrf
                <div class="row"> 
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Voucher No</label>
                            <input type="text" value="@if (!empty(old('voucher_no'))) {{old('voucher_no')}} @else {{'BADDEBT'.time()}} @endif" name="voucher_no" readonly class="form-control">
                        </div>
                    </div>                   
                    <div class="col-sm-4">                        
                        <div class="form-group mb-3">
                            <label for="" id="">Store <span class="text-danger">*</span></label>
                            
                            
                            <input type="text" name="store_name" class="form-control" id="store_name" placeholder="Search store by name" onkeyup="getStores(this.value);" value="{{ old('store_name') }}" autocomplete="off">
                            <input type="hidden" name="store_id" id="store_id" value="{{ old('store_id') }}">
                            <div class="respDropStore" id="respDropStore" style="position: relative;"></div>
                                    
                            
                            @error('store_id') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>                                        
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Amount <span class="text-danger">*</span></label>
                            <input type="text" value="{{ old('amount') }}" name="amount" class="form-control"  id="amount" >
                            
                            
                            @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="payment_date" max="{{date('Y-m-d')}}" class="form-control" @if(old('payment_date')) value="{{ old('payment_date') }}" @else value="{{ date('Y-m-d') }}" @endif>
                            @error('payment_date') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div> 
                    <div class="col-sm-4">                        
                        <div class="form-group mb-3">
                            <label for="">Mode of Payment <span class="text-danger">*</span></label>
                            <select name="payment_mode" class="form-control" @if(!empty($payment_collection)) disabled @endif  id="payment_mode">
                                <option value="" selected hidden>Select One</option>
                                <option value="cheque" @if(old('payment_mode') == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if(old('payment_mode') == 'neft') selected @endif>NEFT</option>
                                <option value="cash" @if(old('payment_mode') == 'cash') selected @endif>Cash</option>
                            </select>
                            @error('payment_mode') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Narration </label>
                            <textarea name="narration" class="form-control" id="" cols="30" rows="1" placeholder="Enter some text ... ">{{ old('narration') }}</textarea>                          
                        </div>
                    </div>
                </div>
                <div class="row" id="noncash_sec">                    
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Cheque No / UTR No </label>
                            
                            <input type="text"  value="{{ old('chq_utr_no') }}" name="chq_utr_no" class="form-control" maxlength="100">
                            
                            
                            @error('chq_utr_no') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group mb-3">
                            <label for="">Bank Name </label>
                            <div id="bank_search">
                                
                                <input type="text" id="" placeholder="Search Bank" name="bank_name" value="{{ old('bank_name') }}" onkeyup="getBankList(this.value);" class="form-control bank_name" maxlength="200">
                                <input type="hidden" class="form-control"  name="bank_name_hidden" value="{{ old('bank_name') }}"  id="bank_name">
                                <div class="resBankProp"></div>
                                                                
                            </div>                                                      
                        </div>
                    </div>                    
                </div>
   
                <div class="row">
                    <div class="form-group">
                        <a href="{{ route('admin.accounting.list_bad_debt') }}" class="btn btn-sm btn-danger">Back</a>
                        <button type="submit" id="submit_btn" class="btn btn-sm btn-success">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<script>

    $(document).ready(function(){
        
                  
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

        $.ajax({
            url: "{{ route('admin.ledger.getStoreLedgerAmount') }}",
            method: 'post',
            data: {
                '_token': '{{ csrf_token() }}',
                store_id: id
            },
            success: function(result) {

              let outstanding = result.outstanding;
              let amount = outstanding.toString().replace('-','')
                console.log(amount);
                $('#amount').val(amount)
                
            }
        });
        
        
    }

</script>
@endsection
