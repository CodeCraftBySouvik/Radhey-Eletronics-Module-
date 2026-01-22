@extends('admin.layouts.app')
@section('page', 'Profit & Loss')
@section('content')
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Profit & Loss</li>
    </ul>   
    <form action="">
        <div class="row justify-content-end pb-3">
            <div class="col-6 col-sm-2">
                <label for="from_date">From</label>
                <input type="date" name="from_date"  class="form-control select-md" value="{{ $from_date }}" max="{{ $to_date }}" min="{{ $min_from_date }}"  autocomplete="off">
            </div>
            <div class="col-6 col-sm-2">
                <label for="to_date">To</label>
                <input type="date" name="to_date"  class="form-control select-md"  value="{{ $to_date }}" placeholder="To" max="{{ date('Y-m-d') }}" min="{{ $from_date }}" autocomplete="off">
            </div>
            <div class="col-6 col-sm-2">
                <label for="to_date">.</label>
                <div class="form-group">
                    <button type="submit" class="btn btn-outline-success select-md">Search</button>
                    <a href="{{route('admin.revenue.index')}}" class="btn btn-outline-warning select-md">Reset</a>
                </div>
            </div>
        </div>
    </form>
    @if (Session::has('message'))
    <div class="alert {{ Session::get('alert-class') }}" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    
    @php
        $net_value = 0;
    @endphp
    <div class="row">
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Total Sales
                    </h6>
                    <h4>Rs. {{$total_sales}}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Total Expense
                    </h6>
                    <h4>Rs. {{$total_expense}}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Net Profit (Total Revenue - Total Expense)
                    </h6>
                    <h4>Rs. {{ $net_profit }} </h4>
                    {{-- <h4>Rs. {{ replaceMinusSign($net_profit) }} {{getCrDr($net_profit)}} </h4> --}}
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Net Profit Margin
                    </h6>
                    <h4> {{ $net_profit_margin }} % </h4>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Revenue Collected
                    </h6>
                    <h4>Rs. {{$total_revenue_collected}}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Profit in Hand
                    </h6>
                    <h4>Rs. {{ $profit_in_hand }}</h4>
                    {{-- <h4>Rs. {{ replaceMinusSign($profit_in_hand) }} {{getCrDr($profit_in_hand)}}</h4> --}}
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Gross Profit [(Goods sold) - (Sold Goods Purchased at what amount) + (service slip)]
                    </h6>
                    <h4>Rs. {{ $gross_profit }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Gross Profit %
                    </h6>
                    <h4>{{$gross_profit_percentage}}%</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Reserved Amount
                    </h6>
                    <h4>Rs. {{ $reserved_amount }} </h4>
                    {{-- <h4>Rs. {{ replaceMinusSign($reserved_amount) }} {{getCrDr($reserved_amount)}} </h4> --}}
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-lg-3">
            <div class="card home__card bg-gradient-secondary">
                <div class="card-body">
                    <h6>
                        Withdrawable Amount (Each Partner)
                    </h6>
                    {{-- <h4>Rs. {{ replaceMinusSign($withdrawable_amount_each) }} {{getCrDr($withdrawable_amount_each)}} </h4> --}}
                    <h4>Rs. {{ $withdrawable_amount_each }} </h4>
                </div>
            </div>
        </div>
    </div>
    <div class="search__filter">
        <div class="row align-items-center justify-content-between">        
            <div class="">
                <div class="row">
                    <h5>Journal</h5>
                    <div class="col-12">
                        <form action="{{ route('admin.revenue.index') }}" method="GET" id="revForm">
                            <div class="row g-3 align-items-end">                                
                                
                                @error('from_date') <p class="small text-danger">{{ $message }}</p> @enderror
                                <div class="col-6 col-sm-2">
                                    <label for="from_date">From</label>
                                    <input type="date" name="from_date" id="from_date"  class="form-control select-md date-val" value="{{ $from_date }}" max="{{ $to_date }}" min="{{ $min_from_date }}"  autocomplete="off">
                                </div>
                                <div class="col-6 col-sm-2">
                                    <label for="to_date">To</label>
                                    <input type="date" name="to_date" id="to_date"  class="form-control select-md date-val"  value="{{ $to_date }}" placeholder="To" max="{{ date('Y-m-d') }}" min="{{ $from_date }}" autocomplete="off">
                                </div>
                                <div class="col-6 col-sm-2">
                                    <div class="form-group">
                                        <label for="">Bank / Cash</label>
                                        <select name="bank_cash" onchange="this.form.submit();" id="bank_cash" class="form-control select-md dates">
                                            <option value="" @if(empty($bank_cash)) selected @endif>All</option>
                                            <option value="bank" @if($bank_cash == 'bank') selected @endif>Bank</option>
                                            <option value="cash" @if($bank_cash == 'cash') selected @endif>Cash</option>
                                        </select> 
                                    </div>
                                </div>
                                <div class="col-6 col-sm-2">
                                    <div class="form-group">
                                        <a href="javascript:void(0)" onclick="downloadJournal('csv');" class="btn btn-outline-success select-md">Download CSV</a>
                                    </div>
                                </div>
                                <div class="col-auto ms-auto">                                    
                                    @if (Auth::user()->type == 1)
                                    <a href="{{ route('admin.revenue.withdraw_form') }}" class="btn btn-sm btn-outline-success select-md">Withdraw</a>
                                    @endif
                                    
                                </div>
                            </div>                           
                        </form>
                    </div>          
                </div>                
            </div>
        </div>
    </div>                
    <div class="table-responsive">
        <table class="table table-sm table-hover ledger">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction Id / Voucher No</th>       
                    <th>Purpose</th>   
                    <th>Debit</th>      
                    <th>Credit</th>
                    <th>Closing</th>
                </tr>
            </thead>
            <tbody>
                @php                
                    $net_value = $all_deductions = 0;
                @endphp
                @forelse ($data as $index => $item)   
                
                @php
                                    
                    $purpose_name = $item->purpose;
                    
                    $debit_amount = $credit_amount = '';
                    if($item->is_credit == 1){
                        $class = "success";
                        $net_value += $item->transaction_amount;
                        $credit_amount = $item->transaction_amount;
                        $crdr = "Cr";
                    }
                    if($item->is_debit == 1){
                        $class = "danger";
                        $net_value -= $item->transaction_amount;
                        $debit_amount = $item->transaction_amount;
                        $crdr = "Dr";
                    }
                    $show_payment_mode = "( ".ucwords($item->bank_cash)." )";
                    // $show_payment_mode_purpose_arr = array('opening_balance','payment_receipt','expense');
                    // if(in_array($item->purpose, $show_payment_mode_purpose_arr)) {
                    //     $show_payment_mode = "( ".ucwords($item->bank_cash)." )";
                    // } else {
                    //     $show_payment_mode = "( Bank )";
                    // }
                @endphp
                    
                <tr class="store_details_row">   
                    <td>{{ date('d/m/Y', strtotime($item->entry_date)) }}</td>
                    <td>{{ $item->purpose_id }}</td>                
                    <td>{{ ucwords(str_replace("_"," ",$item->purpose)) }} {{$show_payment_mode}}</td>
                    <td>
                        <span class="text-danger">{{ $debit_amount }}</span>
                    </td>
                    <td>
                        <span class="text-success">{{ $credit_amount }}</span>
                    </td>
                    <td>
                        {{ replaceMinusSign($net_value) }} {{ getCrDr($net_value) }}
                    </td>
                    {{-- <td><span class="text-{{$class}}">{{ $item->transaction_amount }} {{$crdr}} </span></td> --}}
                </tr>
                <tr>
                    <td colspan="6" class="store_details_column">
                        <div class="store_details">
                            <table class="table">
                                <tr>
                                    <td><span>Purpose: <strong>{{ ucwords(str_replace("_"," ",$item->purpose)) }}</strong></span></td>
                                    <td><span>Description: <strong>{{ ucwords($item->purpose_description) }}</strong></span></td>
                                    <td><span>Amount: <strong>{{$item->transaction_amount}}</strong></span></td>
                                </tr>
                                <tr>                           
                                    @if (!empty($item->payment_mode))
                                    <td><span>Payment Mode: <strong>{{ ucwords($item->payment_mode)}}</strong></span></td>    
                                    @endif
                                    @if (!empty($item->chq_utr_no))
                                    <td><span>Cheque / UTR No: <strong>{{ ucwords($item->chq_utr_no)}}</strong></span></td>    
                                    @endif
                                    @if (!empty($item->narration))
                                    <td><span>Narration: <strong>{{ ucwords($item->narration)}}</strong></span></td>    
                                    @endif
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
                
                @empty
                <tr>
                    <td colspan="100%" class="small text-muted text-center">No data found</td>
                </tr>
                @endforelse

                @if(!empty($data))
                <tr class="table-info">
                    <td colspan="5"><strong>Closing Amount</strong>  </td>
                    <td>                            
                        <strong>                                                               
                            {{ replaceMinusSign($net_value) }} {{ getCrDr($net_value)}}
                        </strong>
                    </td>
                </tr>   
                @endif
                
            </tbody>
        </table>
    </div>        
</section>
<script type="text/javascript">  

    $(document).ready(function(){
        // alert({{$net_value}});
        $('#withdraw_btn').hide();
        var net_value = "{{$net_value}}";
        $('#net_value').val(net_value);
        if(net_value == 0){
            // alert(net_value);
            $('#withdraw_btn').hide();
        }else{
            $('#withdraw_btn').show();
            // $(#withdraw_btn).prop('href', '{{ route("admin.revenue.withdraw_form",['.$month_val.','.$year_val.','.$net_value.']) }}')
        }
    })

    function downloadJournal(e){
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();
        var bank_cash = $('#bank_cash').val();
        
        var dataString = "from_date="+from_date+"&to_date="+to_date+"&bank_cash="+bank_cash ;
        
        if(e == 'csv'){
            window.location.href = "{{ route('admin.revenue.downloadJournalCSV') }}?"+dataString; 
        }
    }

    function getDifferenceInDays(date1, date2) {
        const diffInMs = Math.abs(date2 - date1);
        return diffInMs / (1000 * 60 * 60 * 24);
    }

    $("[type='date']").bind('keyup keydown',function (evt) {
        evt.preventDefault();
        alert('Please choose date by clicking on calender icon');
    });

    $('.date-val').on('change', function(){
        var from_date = $('#from_date').val();
        var to_date = $('#to_date').val();
        var date1 = new Date(from_date);
        var date2 = new Date(to_date);

        console.log(new Date(from_date));
        
        var days = getDifferenceInDays(date1, date2);
        console.log(days);

        // if(days <= 365){
            $('#revForm').submit();
        // }
        // else{
        //     alert('Date range must be under 365 days');
        //     return false;   
        // }
        
    }) 

    // $('.cur_date').on('change', function(){
    //     var month_val = $('#month_val').val();
    //     var year_val = $('#year_val').val();

    //     $('#revForm').submit();
    // })

    
</script>
@endsection
