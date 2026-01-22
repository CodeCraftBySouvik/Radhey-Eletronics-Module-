@extends('admin.layouts.app')
@section('page', ($userType == 1)? 'Customer List' : 'Supplier Management')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-12">  
            <form action="{{ route('admin.user.index', $userType) }}" id="searchForm">          
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
                        <a class="btn btn-outline-success select-md" href="{{ route('admin.user.create', 2) }}">Add New Supplier</a>
                    </div>
                    <div class="col-4">
                        <input type="search" id="term" name="term" class="form-control select-md" placeholder="Search here.." autocomplete="off" value="{{$term}}">                               
                    </div>
                </div>
            </div>
            </form>
            <form action="{{ route('admin.user.supplierBulkSuspend') }}" method="POST">
            @csrf                    
                <div class="filter">
                    <div class="row align-items-center justify-content-between">
                    <div class="col">                                                        
                        <div class="col-auto">
                            <button id="btnSuspend" type="submit" class="btn btn-outline-danger select-md">Suspend</button>
                        </div>                            
                    </div>
                    <div class="col-auto">
                        <p>{{$total}} Total Items</p>
                    </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="check-column">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="checkAll">
                                </div>
                                </th>
                                {{-- <th class="text-center"><i class="fa-fa icon"></i></th> --}}
                                <th>Created At</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $index => $item)
                            <tr>
                                <td class="check-column">
                                    @if ($item->status != 0)
                                    <div class="form-check">
                                        <input name="suspend_check[]" class="data-check" type="checkbox"  value="{{$item->id}}">
                                    </div>
                                    @endif
                                </td>
                                {{-- <td class="text-center column-thumb">

                                    @if($item->gst_file)
                                        <img src="{{asset($item->gst_file)}}" alt="" style="height: 100px" class="mr-4">
                                    @else
                                        <img src="{{asset('admin/images/placeholder-image.jpg')}}" alt="" class="mr-4" style="width: 100px;height: 100px;border-radius: 50%;">
                                    @endif
                                </td> --}}
                                <td>
                                    {{ date('m/d/Y', strtotime($item->created_at)) }}
                                    <div class="row__action">
                                        <a href="{{ route('admin.user.edit', ['userType'=>$userType, 'id'=>$item->id]) }}">Edit</a>
                                        <a href="{{ route('admin.user.view', $item->id) }}">View</a>
                                        <a href="{{ route('admin.user.status', ['id'=>$item->id, 'userType'=>$userType]) }}">{{($item->status == 1) ? 'Suspend' : 'Active'}}</a>
                                        
                                    </div>
                                </td>
                                <td>  {{$item->name}}  </td>
                                <td>
                                    <p class="small text-muted mb-1"> 
                                        
                                        @if(!empty($item->mobile))
                                        <span>Mobile: <strong>{{$item->mobile}}</strong></span> <br/>
                                        @endif
                                        @if (!empty($item->whatsapp_no))
                                        <span>WhatsApp: <strong> {{$item->whatsapp_no}}</strong></span>  <br/>
                                        @endif   
                                        @if (!empty($item->email))
                                        <span>Email ID: <strong> {{$item->email}}</strong></span>  <br/>
                                        @endif
                                    </p>
                                </td>


                                <td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Suspend'}}</span></td>
                                {{-- <td><span class="badge bg-{{($item->is_verified == 1) ? 'success' : 'danger'}}">{{($item->is_verified == 1) ? 'verified' : 'not verified'}}</span></td> --}}
                            </tr>
                            @empty
                            <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            {{$data->links()}}
        </div>
    </div>
</section>
<script>

    $(document).ready(function(){
        $('#btnSuspend').prop('disabled', true);
        $('div.alert').delay(3000).slideUp(300);
        $("#checkAll").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
            var checkAllStatus = $("#checkAll:checked").length;
            var total_checkbox = $('input:checkbox.data-check').length;
            // console.log(checkAllStatus)
            if(checkAllStatus == 1 && total_checkbox > 0){
                $('#btnSuspend').prop('disabled', false);
            }else{
                $('#btnSuspend').prop('disabled', true);
            }
        });

        $('.data-check').change(function () {
            $('#btnSuspend').prop('disabled', false);
            var total_checkbox = $('input:checkbox.data-check').length;
            var total_checked = $('input:checkbox.data-check:checked').length;
            // console.log( total_checkbox);
            // console.log(total_checked);

            if(total_checked == 0){
                $('#btnSuspend').prop('disabled', true);
            }
          
            if(total_checkbox == total_checked){
                console.log('All checked')
                $('#checkAll').prop('checked', true);
            }else{
                console.log('Not All checked')
                $('#checkAll').prop('checked', false);
            }
        })
    });

    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        // alert('Cleared');
        $('#searchForm').submit();
    });

    $('#term').on('keyup', function(){
        var timer;
        clearTimeout(timer);
        timer=setTimeout(()=>{ 
            $('#searchForm').submit();
        },1500);
    });
    
</script>
@endsection