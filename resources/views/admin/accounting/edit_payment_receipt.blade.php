@extends('admin.layouts.app')
@section('page', 'Edit Payment Receipt')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Edit Payment Receipt</li>
    </ul>
    <div class="row">
        @php
            $readonly = "readonly";
            
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
            <form id="payment_form" action="{{ route('admin.accounting.update_payment_receipt') }}" method="POST">
                @csrf   
                <input type="hidden" name="payment_id" value="{{$payment_collection->payment_id}}">
                <input type="hidden" name="old_payment_amount" value="{{ $payment_collection->collection_amount }}">
                <input type="hidden" name="ledger_url" value="{{$ledger_url}}">
                <div class="row">                    
                    <div class="col-sm-4">                        
                        <div class="form-group mb-3">
                            <label for="" id="">Store </label>
                            <input type="text" name="" class="form-control" disabled value="{{$payment_collection->stores->bussiness_name}}" id="">
                            <input type="hidden" name="store_id" value="{{$payment_collection->store_id}}">
                        </div>
                    </div>  
                   
                    <div class="col-sm-4">                        
                        <div class="form-group mb-3">
                            <label for="" id="">Collected By </label>
                            <input type="text" name="" class="form-control" disabled value="{{$payment_collection->users->name}}" id="">
                            <input type="hidden" name="user_id" value="{{$payment_collection->user_id}}">
                        </div>
                    </div>    
                    
                                        
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Amount <span class="text-danger">*</span></label>
                            @if (!empty($payment_collection))
                            <input type="text" value="{{ $payment_collection->collection_amount }}" maxlength="20" name="amount"  class="form-control">
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
                            <input type="text" value="{{$voucher_no}}" name="voucher_no" readonly class="form-control">
                        </div>
                    </div>
                    @php                        
                        // $paymentDate = !empty($payment_collection)?$payment_collection->cheque_date:'';
                    @endphp
                    <div class="col-sm-4">
                        <div class="form-group mb-3">
                            <label for="">Date </label>
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
                            <select name="payment_mode" class="form-control" id="payment_mode">
                                <option value="" selected hidden>Select One</option>
                                @if (!empty($payment_collection))
                                <option value="cheque" @if($payment_collection->payment_type == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if($payment_collection->payment_type == 'neft') selected @endif>NEFT</option>
                                <option value="cash" @if($payment_collection->payment_type == 'cash') selected @endif>Cash</option>
                                @else
                                <option value="cheque" @if(old('payment_mode') == 'cheque') selected @endif>Cheque</option>
                                <option value="neft" @if(old('payment_mode') == 'neft') selected @endif>NEFT</option>
                                <option value="cash" @if(old('payment_mode') == 'cash') selected @endif>Cash</option>  
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
                        <a href="{{ route('admin.report.choose-ledger-user') }}?{{$ledger_url}}" class="btn btn-sm btn-danger">Back</a>
                        <button type="submit" id="submit_btn" class="btn btn-sm btn-success">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-md-12">
            <h5>Invoice Covered</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Invoice Amount</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @forelse ($invoice_payments as $payment)
                    <tr>
                        <td>{{$i}}</td>
                        <td>{{$payment->invoice->invoice_no}}</td>
                        <td>{{ number_format((float)$payment->invoice_amount, 2, '.', '') }}</td>
                        <td>{{ number_format((float)$payment->paid_amount, 2, '.', '') }}</td>
                        <td>{{ number_format((float)$payment->rest_amount, 2, '.', '') }}</td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center;"> No invoice payment covered</td>
                    </tr>
                    @endforelse
                    
                </tbody>
            </table>
        </div>
    </div> --}}
</section>
<script>

    $(document).ready(function(){
        
        var paymentCollectionId = 0
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

</script>
@endsection
