@extends('admin.layouts.app')
@section('page', 'Withdrawn')
@section('content')
<section> 
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li><a href="{{ route('admin.revenue.index') }}">Profit & Loss</a></li>
        <li>Withdrawn </li>
    </ul>   
    @if (Session::has('message'))
        <div class="alert alert-success" role="alert">
            {{ Session::get('message') }}
        </div>
    @endif  
    <div class="col-auto" id="withdrawn_div">
        <span class="text-danger" id="warning_withdraw_span"></span>
        <form action="{{ route('admin.revenue.withdraw_partner_amount') }}" method="POST">
        @csrf
        <input type="hidden" name="entry_date" id="entry_date" value="{{ date('Y-m-d') }}">
        <input type="hidden" name="admin_id" id="" value="{{ Auth::user()->id }}">
        
        <div class="row">
            @php
                $no = genAutoIncreNoInv($table='withdrawls',5);
                $voucher_no = "WITHDRW".date("Y").$no;
            @endphp
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="lable_user">Voucher No </label>
                    <input type="text" name="voucher_no" class="form-control" readonly id="voucher_no" value="{{ $voucher_no }}">
                </div>
            </div>  
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="lable_user">Withdrawlable Amount </label>
                    <input type="text" name="withdrawable_amount" class="form-control" id="" value="{{ $withdrawable_amount_each }}" readonly>
                </div>
            </div> 
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="lable_user">Reserved Amount </label>
                    <input type="text" name="reserved_amount" class="form-control" id="" value="{{ $reserved_amount }}" readonly>
                </div>
            </div> 
        </div>
        <div class="row">
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="lable_user">Profit in Hand </label>
                    <input type="text" name="profit_in_hand" class="form-control" id="" value="{{ $profit_in_hand }}" readonly>
                </div>
            </div> 
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="lable_user">Net Profit </label>
                    <input type="text" name="net_profit" class="form-control" id="" value="{{ $net_profit }}" readonly>                    
                </div>
            </div>         
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="lable_user">Net Profit Margin </label>
                    <input type="text" name="net_profit_margin" class="form-control" id="" value="{{ $net_profit_margin }}" readonly>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="lable_user">Withdrawl Amount <span class="text-danger">*</span> </label>
                    <input type="text" name="amount" class="form-control" id="amount" value="@if(!empty(old('amount'))){{old('amount')}} @else {{ $withdrawable_amount_each }} @endif">
                    @error('amount') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="">Entry Date <span class="text-danger">*</span> </label>
                    <input type="date" name="entry_date" max="{{ date('Y-m-d') }}" class="form-control" id="amount" value="{{ old('entry_date') ? old('entry_date') : date('Y-m-d') }}">
                    @error('entry_date') <p class="small text-danger">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="col-sm-4">                        
                <div class="form-group mb-3">
                    <label for="" id="">Mode Of Payment <span class="text-danger">*</span> </label>
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
            <div class="col-sm-6">                        
                <div class="form-group mb-3">
                    <a href="{{ route('admin.revenue.index') }}" class="btn btn-sm btn-danger">Back</a>
                    <button type="submit" id="" class="btn btn-sm btn-success">Submit</button>
                    
                </div>
            </div>
        </div>
        </form>
    </div>    
</section>
<script>
    $(document).ready(function(){
        
        var old_payment_mode = "{{ old('payment_mode') }}";
        if(old_payment_mode != ''){
            if(old_payment_mode == 'cash'){
                $('#noncash_sec').hide();
            } else {
                $('#noncash_sec').show();
            }
        } else {
            $('#noncash_sec').hide();
        }
    })
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
