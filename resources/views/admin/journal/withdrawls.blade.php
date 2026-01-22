@extends('admin.layouts.app')
@section('page', 'Partner Withdrawls')
@section('content')
<section> 
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Partner Withdrawls</li>
    </ul>    
    <div class="search__filter">
        <div class="row align-items-center justify-content-between">        
            <div class="col-auto">
                <div class="row">
                    @if ($auth_type == 1)
                    <h5>My Withdrawls</h5>
                    @else
                    <h5>Withdrawls</h5>                        
                    @endif
                    <div class="col-auto">
                        <form action="" method="GET" id="">
                                                   
                        </form>
                    </div>          
                </div>                
            </div>
        </div>
    </div>  
                
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
                <th>Requested At</th>
                <th>Partner</th>
                <th>Withdrawl Percentage <br/> (As per last changed settings)</th>
                <th>Withdrawl Amount</th>  
                <th>Required Amount</th>     
                <th>Withdrawable Amount</th>   
                <th>Reserved Amount</th>      
                <th>Net Profit</th>
                <th>Net Profit Margin</th>
                {{-- <th>Action</th> --}}
            </tr>
        </thead>
        <tbody>
            @php
                if(empty(Request::get('page')) || Request::get('page') == 1){
                    $i=1;
                } else {
                    $i = (((Request::get('page')-1)*$paginate)+1);
                }                 
            @endphp
            @forelse ($withdrawls as $index => $item)                     
            <tr class="store_details_row">
                <td>{{ $i }}</td>                
                <td>
                    
                    {{ $item->voucher_no }}
                    <br/>
                    {{ date('d/m/Y', strtotime($item->entry_date)) }}
                </td>
                <td>
                    <p class="small text-muted mb-1">
                        <span>{{ $item->name }}</span> <br/>
                    </p>
                </td>
                <td>{{ $item->withdrawable_percentage }} %</td>
                <td>{{ $item->withdrawl_amount }}</td>    
                <td>{{ $item->required_amount }}</td>            
                <td>{{ $item->withdrawable_amount }}</td> 
                <td>{{ $item->reserved_amount }}</td> 
                <td>{{ $item->net_profit }}</td> 
                <td>{{ $item->net_profit_margin }}</td> 
                {{-- <td>
                    @if (empty($item->is_disbursed))
                        @if ($auth_type == 2)
                            <a href="{{ route('admin.accounting.add_expense_partner_withdrawls', $item->id) }}" class="btn btn-sm btn-success">Disburse</a>
                        @else
                            @if (Auth::user()->id == $item->admin_id)
                                <a href="{{ route('admin.revenue.delete_request', $item->id) }}" onclick="return confirm('Are you sure want to delete?');" class="btn btn-sm btn-danger">Delete</a>
                            @endif

                        @endif
                        
                    @else
                        <span class="badge bg-success">Disbursed</span>
                    @endif
                   
                </td> --}}
            </tr>
            
            @php
                $i++;
            @endphp
            @empty
            <tr>
                <td colspan="100%" class="small text-muted text-center">No data found</td>
            </tr>
            @endforelse
            
        </tbody>
    </table>
    </div>
    
</section>
<script>
    $(document).ready( function(){
        $('div.alert').delay(3000).slideUp(300);
    })
</script>
@endsection
