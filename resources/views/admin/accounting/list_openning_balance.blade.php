@extends('admin.layouts.app')
@section('page', 'Customer Opening Balance')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Customer Opening Balance</li>
    </ul>
    <div class="row">
        <div class="col-md-12">           
            @if (Session::has('message'))
            <div class="alert alert-success" role="alert">
                {{ Session::get('message') }}
            </div>
            @endif           
            <div class="table-responsive"> 
            <table class="table table-sm table-hover ledger">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @forelse ($data as $item)

                    @php
                        if($item->is_credit == 1){
                            $class = "success";
                        }
                        if($item->is_debit == 1){
                            $class = "danger";
                        }
                    @endphp
                    
                    <tr class="store_details_row">  
                        <td>{{$i}}</td>
                        <td>
                            <p class="m-0">
                                {{date('d/m/Y', strtotime($item->entry_date))}}
                            </p>
                        </td>    
                        <td>{{ $item->transaction_id }}</td>
                        <td>
                            <p class="m-0">
                                {{$item->store_name}}
                            </p>
                        </td> 
                        <td>
                            <span class="text-{{$class}}">
                                {{$item->transaction_amount}} ( {{ucwords($item->bank_cash)}} )
                            </span>
                        </td>    
                        <td>
                            <a href="{{ route('admin.accounting.deleteopeningbalance', $item->id) }}" onclick="return confirm('Are you sure want to delete ? ');" class="btn btn-sm btn-danger delete_btn">Delete</a>
                        </td>                                   
                    </tr>
                    <tr>                        
                        <td colspan="5" class="store_details_column">
                            <div class="store_details">
                                <table class="table">
                                    <tr>
                                        <td>
                                            <span>Person Name: <strong>{{$item->store_name}} </strong> </span> 
                                        </td>
                                        @if (!empty($item->bussiness_name))
                                        <td>
                                            <span>Company Name: <strong>{{$item->bussiness_name}} </strong> </span> 
                                        </td> 
                                        @endif  
                                        @if (!empty($item->contact))
                                        <td>                                            
                                            <span>Phone: <strong>{{$item->contact}} </strong> </span>  
                                        </td>  
                                        @endif    
                                        @if (!empty($item->whatsapp))                                      
                                        <td>
                                            <span>WhatsApp: <strong>{{$item->whatsapp}} </strong> </span> 
                                        </td>  
                                        @endif
                                    </tr>
                                    <tr>
                                        <td><span>Purpose: <strong>{{ ucwords(str_replace("_"," ",$item->purpose)) }}</strong></span></td>
                                        <td><span>Description: <strong>{{ ucwords($item->purpose_description) }}</strong></span></td>
                                        <td><span>Amount: <strong>{{$item->transaction_amount}}</strong></span></td>
                                        <td><span>Credit / Debit: <strong>{{!empty($item->is_debit) ? 'Debit': 'Credit'}}</strong></span></td>
                                    </tr>
                                    <tr>                           
                                        @if (!empty($item->payment_mode))
                                        <td><span>Payment Mode: <strong>{{ ucwords($item->payment_mode)}}</strong></span></td>    
                                        @endif
                                        @if (!empty($item->bank_name))
                                        <td><span>Bank: <strong>{{ ucwords($item->bank_name)}}</strong></span></td>    
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
                    @php
                        $i++;
                    @endphp
                    @empty
                    <tr>
                        <td colspan="100%">
                            <span></span>
                        </td>
                    </tr>    
                    @endforelse
                </tbody>
            </table>   
        </div>
            {{$data->links()}}
        </div>
    </div>
</section>
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    })
</script>
@endsection
