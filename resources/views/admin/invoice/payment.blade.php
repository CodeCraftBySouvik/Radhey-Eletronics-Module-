@extends('admin.layouts.app')
@section('page', 'Payments')
@section('content')
<section>
    @if(!empty($user_type))
    <ul class="breadcrumb_menu">
        <li>Master</li>        
        @if($user_type == 'staff')
        <li><a href="{{ route('admin.staff.index') }}">Staff Management</a> </li> 
        @elseif ($user_type == 'store')
        <li><a href="{{ route('admin.store.index') }}">Store Management</a> </li> 
        @endif

        @if($user_type == 'staff')
        <li><a href="{{ route('admin.invoice.staff', $user_id) }}">Invoice</a> </li>  
        @elseif ($user_type == 'store')
        <li><a href="{{ route('admin.invoice.store', $user_id) }}">Invoice</a> </li>  
        @endif
        <li>Payments</li>
    </ul>
    @else
    <ul class="breadcrumb_menu">
        <li>Sales</li>
        <li><a href="{{ route('admin.invoice.index') }}">Invoice</a> </li>
        <li>Payments</li>
    </ul>
    @endif
    <div class="row">
        <div class="col-sm-12">
            <div class="search__filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        
                    </div>                    
                    <div class="col-auto">
                        
                    </div>
                    <div class="col-auto">
                        
                    </div>
                </div>
            </div>
            
            <div class="filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col-auto">                            
                        {{-- <p> Items</p> --}}
                    </div>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>      
                        <th>#</th>    
                        <th>Payment Date</th>  
                        <th>Order Creator</th>
                        <th>Payment Collector</th>
                        <th>Voucher No</th>
                        <th>Invoice Amount</th>
                        <th>Voucher Amount</th>
                        <th>Paid Amount</th>
                        <th>Due Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @forelse ($data as $index => $item)

                    
                    <tr>        
                        <td>{{ $i }}</td>   
                        <td>{{ date('d/m/Y', strtotime($item->paymentcollection->cheque_date)) }}</td> 
                        <td>
                            {{ $item->invoice->order->users->name }}
                            {{-- {{ $item->order_creator_name }} --}}
                        </td>
                        <td>
                            {{ $item->paymentcollection->users->name }}
                            {{-- {{ !empty($item->payment_collector_name)?$item->payment_collector_name:$item->payment_collector_partner_name }} --}}
                        </td>
                        <td>{{ $item->voucher_no }}</td>
                        <td>{{ 'Rs. '.number_format((float) $item->invoice_amount, 2, '.', '') }}</td>
                        <td>{{ 'Rs. '.number_format((float) $item->vouchar_amount, 2, '.', '') }}</td>
                        <td>{{ 'Rs. '.number_format((float) $item->paid_amount, 2, '.', '') }}</td>
                        <td>{{ 'Rs. '.number_format((float) $item->rest_amount, 2, '.', '') }}</td>
                    </tr>
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                    @endforelse
                </tbody>
            </table>              
            {{-- {{$data->links()}} --}}
        </div>       
    </div>
</section>
<script>
    
    
</script>
@endsection

@section('script')

@endsection