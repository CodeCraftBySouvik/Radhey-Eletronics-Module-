@extends('admin.layouts.app')
@section('page', 'User Ledger')
@section('content')
<style>
    .green_class{
        background: #a3e5a7 !important;
    }
    .yellow_class{
        background: #fffeb1 !important;
    }
    .red_class{
        background: #ffb1bb !important;
    }
    #ledger_body .form-control{
        width: 45% !important;
        cursor: pointer;
    }
</style>
<section>  
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li><a href="{{ route('admin.whatsapp_ledger_user') }}">User Ledger</a> </li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="" id="ledgerForm">
    <div class="row">       
        {{-- <div class="col-12">
            <div class="row g-3 align-items-end">
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">From</label>
                        <input type="date" name="from_date"  id="from_date" class="form-control select-md dates" value="{{ $from_date }}"  max="{{ $to_date }}" placeholder="From" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">To</label>
                        <input type="date" name="to_date"  id="to_date" class="form-control select-md dates" value="{{ $to_date }}" placeholder="To" max="{{ date('Y-m-d') }}" min="{{ $from_date }}" autocomplete="off">  
                    </div>
                </div>                         
            </div>
        </div>  --}}
        <div class="col-12">
            <div class="row g-3 align-items-end">                
                <div class="col-12 col-sm-2">
                    <div class="form-group">
                        <label for="">Choose user type <span class="text-danger">*</span> </label>
                        <select name="user_type" id="user_type" class="form-control select-md" onchange="getUserTypes(this.value);">
                            <option value="" hidden selected>Select an option</option>
                            <option value="store" @if($user_type == 'store') selected @endif>Customer</option>  
                            {{-- <option value="staff" @if($user_type == 'staff') selected @endif>Staff</option>
                            <option value="partner" @if($user_type == 'partner') selected @endif>Partner</option>  
                            <option value="supplier" @if($user_type == 'supplier') selected @endif>Supplier</option>                                   --}}
                        </select>  
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="" id="lable_user">
                            @if (empty($user_type))
                            Search user 
                            @else 
                                @if ($user_type == 'store')
                                    Customer
                                @else
                                    {{ucwords($user_type)}}    
                                @endif
                            @endif                    
                        </label> 
                        <span class="text-danger">*</span>                           
                        
                        <input type="hidden" name="staff_id" id="staff_id" value="{{ $staff_id }}">
                        <input type="hidden" name="store_id" id="store_id" value="{{ $store_id }}">
                        <input type="hidden" name="admin_id" id="admin_id" value="{{ $admin_id }}">
                        <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $supplier_id }}">
                        <input type="text" name="select_user_name" value="{{ $select_user_name }}" placeholder="Please choose user type first and type name of the user" class="form-control select-md"  id="select_user_name" onkeyup="getUsers(this.value);" autocomplete="off">       
                    </div>
                    <div class="respDrop"></div>
                </div>
                <div class="col-auto ms-auto">
                    {{-- @if (!empty($user_type) && !empty($select_user_name))
                        <a href="{{ route('admin.report.user-ledger-pdf') }}?from_date={{$from_date}}&to_date={{$to_date}}&user_type={{$user_type}}&staff_id={{$staff_id}}&store_id={{$store_id}}&admin_id={{$admin_id}}&supplier_id={{$supplier_id}}&select_user_name={{$select_user_name}}&sort_by={{$sort_by}}&bank_cash={{$bank_cash}}" class="btn btn-outline-secondary select-md"><img src="{{asset('img/whatsapp.png')}}" alt="" width="12%"> Send by Whatsapp</a>
                    @endif --}}
                    @if (!empty($user_type) && !empty($select_user_name))
                    <a href="{{ route('admin.whatsapp_ledger_user') }}" class="btn btn-outline-warning select-md">Reset Page</a>
                    @endif
                    
                </div>          
            </div>
        </div>               
    </div>
    </form>
    <div class="row">
        <div class="col-sm-12">    
            <div class="table-responsive">
                <table class="table table-sm table-hover ledger">
                    <thead>
                        <tr> 
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Party Name</th>
                            <th>Message Status</th>
                            <th>Action</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="ledger_body">  
                        @if(count($data)>0)            
                            @foreach($data as $key => $item)
                            @php
                            if($item->whatsapp_status==0){
                                $status_color = 'yellow_class';
                            }elseif($item->whatsapp_status==1){
                                $status_color = 'green_class';
                            }else{
                                $status_color = 'red_class';
                            }
                            @endphp
                            <tr class="store_details_row">
                                <td class="d-flex">
                                    <input type="date" name="start_date" id="start_date{{$item->id}}" value="{{ $item->start_date?date('Y-m-d', strtotime($item->start_date)):date('Y-m-d', strtotime($item->first_date)) }}" class="form-control mr-1">
                                    <button type="button" class="btn btn-success btn-sm mt-1" onclick="UpdateStartDate({{$item->id}})">UPDATE</button>
                                </td>
                                <td>
                                    {{ date('d/m/Y h:i A', strtotime($item->last_date)) }}
                                </td>
                                <td>{{ ucwords($item->bussiness_name) }}</td>                        
                                <td id="counter_{{$item->id}}">
                                    @if($item->whatsapp_status==0)
                                        {{-- @if(LeftWhatsappCounter($item->id)=="Date Expired") --}}
                                        <span>Pending</span>
                                        {{-- @else
                                        {{LeftWhatsappCounter($item->id)}}
                                        @endif --}}
                                    @elseif($item->whatsapp_status==1)
                                        @if($item->last_whatsapp)
                                        last sent at: {{ date('d/m/Y h:i A', strtotime($item->last_whatsapp)) }}
                                        @else
                                        <span>Sent</span>
                                        @endif
                                    @else
                                    <span>Cancelled</span>
                                    @endif
                                </td>  
                                <td>
                                    @php
                                        $start_date = $item->start_date?$item->start_date:$item->first_date;
                                    @endphp
                                    @if($item->whatsapp_status==0)
                                    <a href="#" onclick="WhatsAppCancelLedger({{$item->id}})" class="btn select-md btn-outline-danger">Cancel</a>
                                    @endif
                                    @if($item->whatsapp_status==2)
                                        {{-- <a href="#" class="btn select-md btn-outline-danger">Cancelled</a> --}}
                                        <a href="#" onclick="WhatsAppActiveLedger({{$item->id}})" class="btn select-md btn-outline-primary">Active</a>
                                    @else
                                        @if($item->whatsapp)
                                        <a href="{{route('admin.whats-app.send_ledger_text_whatsapp_message')}}?from_date={{date('Y-m-d', strtotime($start_date))}}&to_date={{date('Y-m-d', strtotime($item->last_date))}}&user_type=store&staff_id=&store_id={{$item->store_id}}&admin_id=&supplier_id=&ledger_id={{$item->id}}&select_user_name={{$item->bussiness_name}}&whatsapp={{$item->whatsapp}}" class="btn select-md btn-outline-success sendLedgerMessageBtn ">Send</a>
                                        @else
                                        <a href="#" class="btn select-md btn-outline-secondary">No whatsapp</a>
                                        @endif
                                    @endif
                                    <a href="{{ route('admin.whatsapp-user-ledger-pdf') }}?from_date={{date('Y-m-d', strtotime($start_date))}}&to_date={{date('Y-m-d', strtotime($item->last_date))}}&user_type=store&staff_id=0&store_id={{$item->store_id}}&admin_id=0&supplier_id=0&select_user_name={{$item->bussiness_name}}&sort_by=asc&bank_cash=" class="btn select-md btn-outline-secondary">Download</a>
                                </td>
                                <td class="{{$status_color}}">

                                </td>
                            @endforeach
                        @endif
                    </tbody>
                </table> 
                {{$data->links()}}  
            </div>
        </div>        
    </div> 
       
</section>
<style>
    /* .table.ledger tr:last-child td {
        background-color: #bee5eb;
    } */

</style>
<script>
     document.querySelectorAll('.sendLedgerMessageBtn').forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default action of the button

            var userConfirmed = confirm('Are you sure you want to send the message?');
            if (userConfirmed) {
                window.location.href = this.href; // Redirect to the href of the button if confirmed
            }
        });
    });
     function UpdateStartDate(id) {
        // Get the ID from the button's data attribute
        var start_date = $('#start_date'+id).val();
        // Make an AJAX request
        $.ajax({
            url: "{{route('admin.update_ledger_start_date')}}", // Specify the URL for the AJAX request
            type: 'POST', // Specify the HTTP method (e.g., GET, POST, etc.)
            data: {
                id: id,
                start_date: start_date,
                // Include CSRF token
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                // Handle the successful response from the server
                if(response.success==true){
                    location.reload();
                }else{
                    console.log('Error:', response.message);
                }
                
            },
            error: function(xhr, status, error) {
                // Handle errors that occur during the AJAX request
                console.error('Error:', error);
            }
        });
    }
    function WhatsAppCancelLedger(id) {
        if (confirm("Are you sure you want to cancel?")) {
            window.location.href = "{{ route('admin.whats-app.ledger_cancel', ':id') }}".replace(':id', id);
        }
    }
    function WhatsAppActiveLedger(id) {
        if (confirm("Are you sure you want to active?")) {
            window.location.href = "{{ route('admin.whats-app.ledger_active', ':id') }}".replace(':id', id);
        }
    }
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);

        @if (!empty($store_ids))
            $('#storeDiv').show();
        @else
            $('#storeDiv').hide();
        @endif
    })
    
    function orderModal(order_id){
        $('#orderModal').modal({
            keyboard: true,
            backdrop: "static"
        });
    };

    $("[type='date']").bind('keyup keydown',function (evt) {
        evt.preventDefault();
        alert('Please choose date by clicking on calender icon');
    });

    $('.dates').on('change', function(){
        // var timer;
        // clearTimeout(timer);
        // timer=setTimeout(()=>{            
            $('#ledgerForm').submit();
        // },3000);
    });   
    

    function getUserTypes(evt)
    {
        if(evt != ''){
            $('#select_user_name').val('');
            $('#store_id').val('');
            $('#staff_id').val('');
            $('#admin_id').val('');   
            $('#supplier_id').val('');            
            if(evt == 'staff'){                
                $('#select_user_name').attr("placeholder", "Please type name of staff");
                $('#lable_user').html('Staff');
            }else if(evt == 'store'){                
                $('#select_user_name').attr("placeholder", "Please type name of customer");
                $('#lable_user').text('Customer');
            }else if(evt == 'partner'){                
                $('#select_user_name').attr("placeholder", "Please type name of partner");
                $('#lable_user').text('Partner');
            } else if(evt == 'supplier'){                
                $('#select_user_name').attr("placeholder", "Please type name of supplier");
                $('#lable_user').text('Supplier');
            }            
        }else{
            $('#select_user_name').val('');
            $('#store_id').val('');
            $('#staff_id').val('');
            $('#admin_id').val('');  
            $('#supplier_id').val('');              
            $('#ledger_body').html('');
            $('#select_user_name').val('');
            $('#select_user_name').attr("placeholder", "");
            
        }        
    } 

    function getUsers(evt){
        var user_type = $('#user_type').val();
        if(evt.length > 0){            
            $.ajax({
                url: "{{ route('admin.ledger.getUsersByType') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: evt,
                    type: user_type
                },
                success: function(result) {
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 user-dropdown " aria-labelledby="dropdownMenuButton">`;
                        
                        if(user_type == 'store'){                            
                            $.each(result, (key, value) => {
                            if(value.bussiness_name != ''){
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.bussiness_name}')">${value.bussiness_name}</a>`;
                            } else {
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}')">${value.name}</a>`;
                            }
                            
                        })
                        } else {
                            $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}')">${value.name}</a>`;
                        })
                        }
                        
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 user-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No user found</li></div>`;
                    }
                    $('.respDrop').html(content);
                }
            });
            
        }else{
            $('.respDrop').text('');            
            $('#ledger_body').html('');
            
        }
    }

    function fetchCode(id,name) {
        var user_type = $('#user_type').val();
        $('.user-dropdown').hide()

        $('#store_id').val(0);
        $('#staff_id').val(0);
        $('#admin_id').val(0);
        $('#supplier_id').val(0);
        if(user_type == 'store'){
            $('#store_id').val(id)
            
        }else if(user_type == 'staff'){
            $('#staff_id').val(id)
            
        }else if(user_type == 'partner'){
            $('#admin_id').val(id)
            
        }else if(user_type == 'supplier'){
            $('#supplier_id').val(id)
            
        }
        $('#select_user_name').val(name);        
        $('#ledgerForm').submit();
    }

    function getDifferenceInDays(date1, date2) {
        const diffInMs = Math.abs(date2 - date1);
        return diffInMs / (1000 * 60 * 60 * 24);
    }
    $(document).ready(function() {
        function updateContent() {
            @php 
                if (count($data) > 0){
                    foreach($data as $key => $item){
                        if($item->whatsapp_status==0) {
                            echo 'var itemId_' . $key . ' = ' . json_encode($item->id) . ';';
                            echo '$.get("../whats-app/ledger-left-whatsapp-counter/" + itemId_' . $key . ', function(response) {';
                            echo '    if (response.counter == "Date Expired") {';
                            echo '        document.getElementById("counter_" + itemId_' . $key . ').innerHTML = "<span>Pending</span>";';
                            echo '    } else {';
                            echo '        document.getElementById("counter_" + itemId_' . $key . ').innerHTML = response.counter;';
                            echo '    }';
                            echo '});';
                        } else if($item->whatsapp_status == 1) {
                            if($item->last_whatsapp) {
                                echo 'document.getElementById("counter_' . $item->id . '").innerHTML = "last sent at: ' . date('d/m/Y h:i A', strtotime($item->last_whatsapp)) . '";';
                            } else {
                                echo 'document.getElementById("counter_' . $item->id . '").innerHTML = "<span>Sent</span>";';
                            }
                        } else {
                            echo 'document.getElementById("counter_' . $item->id . '").innerHTML = "<span>Cancelled</span>";';
                        }
                    }
                }
            @endphp
            console.log('hi');
        }
        // Call updateContent function every second
        setInterval(updateContent, 1000);
    });
    </script>
@endsection
