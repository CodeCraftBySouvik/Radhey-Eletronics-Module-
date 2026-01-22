@extends('admin.layouts.app')
@section('page', 'Store Bad Debt')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Store Bad Debt</li>
    </ul>
    <div class="search__filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                @if (Session::has('message'))
                <div class="alert alert-success" role="alert">
                    {{ Session::get('message') }}
                </div>
                @endif
            </div>
            <div class="col-auto">
                <div class="row">                        
                    <div class="col-auto">
                        <a href="{{ route('admin.accounting.add_bad_debt') }}" class="btn btn-outline-success btn-sm">Add New Bad Debt</a>
                    </div>
                    <div class="col">   
                                    
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                
            </div>
            
            <div class="col-auto">    
                {{-- <p>Total {{$countData}} Items</p>             --}}
            </div>
        </div>
    </div>  
    <div class="row">
        <div class="col-md-12">           
                    
            <div class="table-responsive"> 
            <table class="table table-sm table-hover ledger">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Store</th>
                        <th>Entry Date</th>
                        <th>Amount</th>
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
                          {{$item->store->bussiness_name}}
                        </td>
                        <td>
                            <p class="m-0">
                                {{date('d/m/Y', strtotime($item->created_at))}}
                            </p>
                        </td>    
                        
                        <td>
                            Rs. {{number_format((float)$item->amount, 2, '.', '')}} 
                        </td>    
                                                          
                    </tr>
                    <tr>                        
                        <td colspan="5" class="store_details_column">
                            <div class="store_details">
                                <table class="table">                                   
                                    <tr>   
                                        <td><span>Amount: <strong>Rs. {{number_format((float)$item->amount, 2, '.', '')}}</strong></span></td>                        
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
                                    <tr>
                                        @if (!empty($item->creator))
                                            <td><span>Created By: <strong>{{ ucwords($item->creator->name)}}</strong></span></td>  
                                            <td><span>Created At: <strong>{{ date('d/m/Y h:i A', strtotime($item->created_at)) }}</strong></span></td>    
                                        @endif
                                        
                                    </tr>
                                    <tr>
                                        @if (!empty($item->updater))
                                            <td><span>Updated By: <strong>{{ ucwords($item->updater->name)}}</strong></span></td>  
                                            <td><span>Updated At: <strong>{{ date('d/m/Y h:i A', strtotime($item->updated_at)) }}</strong></span></td>    
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
                        <td colspan="100%" style="text-align: center;">
                            <span>No records found</span>
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
    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });
    $('#entry_date').on('change', function(){
        $('#searchForm').submit();
    });
    $("[type='date']").bind('keyup keydown',function (evt) {
        evt.preventDefault();
        alert('Please choose date by clicking on calender icon');
    });

</script>
@endsection
