@extends('admin.layouts.app')
@section('page', 'Customer Management')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-12">
            <form action="{{ route('admin.store.index') }}" id="searchForm">
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
                        <a class="btn btn-outline-success select-md" href="{{ route('admin.store.create') }}">Add New</a>
                    </div>
                    <div class="col-4">   
                                <input type="search" id="term" name="term" class="form-control select-md" placeholder="Search by name or contact " value="{{$term}}" autocomplete="off">
                                                        
                            <input type="submit" hidden />
                       
                    </div>
                </div>
            </div>
            <div class="filter">                
                <div class="row align-items-center justify-content-between">
                    <div class="col">                                                        
                                                   
                    </div>
                    <div class="col-auto">
                        Number of rows: 
                    </div>
                    <div class="col-auto p-0">                        
                        <select class="form-control select-md" id="paginate" name="paginate">
                            <option value="25" @if($paginate == 25) selected @endif>25</option>
                            <option value="50" @if($paginate == 50) selected @endif>50</option>
                            <option value="100" @if($paginate == 100) selected @endif>100</option>
                        </select>
                    </div>                    
                    <div class="col-auto">                            
                        <p>Total {{$total}} Items</p>
                    </div>
            </div>
            </form>
            <form action="{{ route('admin.store.bulkSuspend') }}" method="POST">
            @csrf    
            <div class="filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">                                                        
                        <div class="col-auto">
                            <button id="btnSuspend" type="submit" class="btn btn-outline-danger select-md">Suspend</button>
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
                            <th>#</th>
                            <th width="240px">Created At & Created By</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th width="">Address</th>
                            <th width="180px">Status & Approval</th>
                            <th>Action</th>
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
                        @forelse ($data as $index => $item)
    
                        <tr>
                            <td class="check-column">
                                @if ($item->status == 1)
                                <div class="form-check">
                                    <input name="suspend_check[]" class="data-check" type="checkbox"  value="{{$item->id}}">
                                </div>
                                @endif
                            </td>
                            <td>
                                {{$i}}
                            </td>
                            <td>
                                
                                <span>Created At: </span> <strong>{{ date('d/m/Y', strtotime($item->created_at)) }}</strong> <br/>

                                @if (!empty($item->creator))
                                    <span>Created By: </span> <strong>{{ $item->creator->name }}</strong> <br/>
                                @endif
                                
                            </td>
                            <td>
                                <p class="small text-muted mb-1"> 
                                    @if (!empty($item->store_name))
                                    <span>Person Name: </span> <strong>{{$item->store_name}}</strong> <br/>
                                    @endif 
                                    @if (!empty($item->bussiness_name))
                                    <span>Bussiness Name: </span> <strong>{{$item->bussiness_name}}</strong> <br/>
                                    @endif              
                                <p>       
                            </td>
                            <td>
                                <p class="small text-muted mb-1"> 
                                    @if(!empty($item->contact))
                                    <span>Mobile: <strong>{{$item->contact}}</strong></span> <br/>
                                    @endif
                                    @if (!empty($item->whatsapp))
                                    <span>WhatsApp: <strong> {{$item->whatsapp}}</strong></span>  <br/>
                                    @endif   
                                    @if (!empty($item->email))
                                    <span>Email ID: <strong> {{$item->email}}</strong></span>  <br/>
                                    @endif                                
                                </p>
                               
                            </td>
                            <td>
                                <p class="small text-muted mb-1"> 
                                    @if (!empty($item->billing_address))
                                    <span>Address: </span><strong>{{$item->billing_address}}</strong><br>
                                    @endif
                                    @if (!empty($item->billing_city))
                                    <span>City: </span><strong>{{$item->billing_city}}</strong><br>
                                    @endif
                                    @if (!empty($item->billing_state))
                                    <span>State: </span><strong>{{$item->billing_state}}</strong>
                                    @endif
                                    
                                </p>
                            </td>
                            <td>
                                @if (!empty($item->status))
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Suspended</span>
                                @endif
                                <br/><br/>
                                @if (!empty($item->is_approved))
                                    <span class="badge bg-success">Approved</span>
                                @else
                                    <span class="badge bg-danger">Not Approved</span>
                                @endif
                            </td>
                            
                            <td>
                                <a href="{{ route('admin.store.edit', $item->id) }}" class="btn btn-outline-success select-md">Edit</a>
                                <a href="{{ route('admin.store.view', $item->id) }}" class="btn btn-outline-success select-md">View</a>
                                <a href="{{ route('admin.store.status', $item->id) }}" class="btn btn-outline-{{($item->status == 1) ? 'danger' : 'success'}} select-md">{{($item->status == 1) ? 'Suspend' : 'Active'}}</a>
                                @if (empty($item->is_approved))
                                <a href="{{ route('admin.store.approve', $item->id) }}" onclick="return confirm('Are you sure want to approve the store?');" class="btn btn-outline-success select-md">Approve</a>
                                @endif
                            </td>
                        </tr>

                        @php
                            $i++;
                        @endphp
                        @empty
                        <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                        @endforelse
                    </tbody>
                </table> 
                {{$data->links()}}
            </div>             
            </form>
        </div>       
    </div>
</section>
<script>

    $(document).ready(function(){
        $('#btnSuspend').prop('disabled', true);
        
        $("#checkAll").change(function () {
            $("input:checkbox").prop('checked', $(this).prop("checked"));
            var checkAllStatus = $("#checkAll:checked").length;
            var total_checkbox = $('input:checkbox.data-check').length;
            console.log(total_checkbox)
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
        $('#searchForm').submit();
    });

    // $('#term').on('keyup', function(){
    //     var timer;
    //     clearTimeout(timer);
    //     timer=setTimeout(()=>{ 
    //         $('#searchForm').submit();
    //     },1500);
    // });
    
</script>
@endsection

{{-- google location section --}}
@section('script')
{{-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDPuZ9AcP4PHUBgbUsT6PdCRUUkyczJ66I&libraries=places,geometry&callback=initMap&v=weekly"></script> --}}
<script>
    // google.maps.event.addDomListener(window,'load',initialize());
    
    // function initialize(){
    //     console.log();
    //     var autocomplete= new google.maps.places.Autocomplete(document.getElementById('address'));
    //     google.maps.event.addListener(autocomplete, 'place_changed', function(){
    //         var places = autocomplete.getPlace();
    //         console.log(places);
    //         var address_details = places.address_components;
    //         address_details.reverse();
    //         $('#address').val(places.formatted_address);
    //         $('#lat').val(places.geometry.location.lat());
    //         $('#lng').val(places.geometry.location.lng());
    //         $('#pin').val(address_details[0].long_name);
    //         $('#state').val(address_details[2].long_name);
    //         $('#city').val(address_details[4].long_name);
        
    //     });
    // }

    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    })
    $('#paginate').change(function(){
        $('#searchForm').submit();
    })
</script>
@endsection