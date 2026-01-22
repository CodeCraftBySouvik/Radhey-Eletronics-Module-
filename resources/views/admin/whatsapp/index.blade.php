@extends('admin.layouts.app')
@section('page', 'Invoices & Packing slips')
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
    .hidden {
        display: none;
    }
    label{
        cursor: pointer;
    }
    .error{
        color: brown !important;
    }
</style>
<section>
    <div class="row">
        <div class="col-sm-12">
            <div class="search__filter">
                <form action="" id="searchForm">  
                <div class="row align-items-center justify-content-between">
                    <div class="col">
                        @if (Session::has('message'))
                        <div class="alert alert-success" role="alert">
                            {{ Session::get('message') }}
                        </div>
                        @endif   
                    </div>    
                    <div class="col mb-2 mb-sm-0">
                        <ul>
                            <li @if(!Request::get('status') || (Request::get('status') == 'all')) class="active" @endif><a href="{{route('admin.whats-app.invoice_list')}}">All</a></li>
                            <li @if(Request::get('status') == 'send' ) class="active" @endif><a href="{{route('admin.whats-app.invoice_list',['status'=>'send'])}}">Sent</a></li>
                            <li @if(Request::get('status') == 'pending' ) class="active" @endif><a href="{{route('admin.whats-app.invoice_list',['status'=>'pending'])}}">Pending</a></li>
                            <li @if(Request::get('status') == 'cancelled' ) class="active" @endif><a href="{{route('admin.whats-app.invoice_list',['status'=>'cancelled'])}}">Cancelled</a></li>
                        </ul>
                    </div>               
                    <div class="col-auto">
                        <select name="type" id="type" class="form-control select-md">
                            <option value="" @if(empty($type)) selected @endif>All Type</option>
                            <option value="gst" @if($type == 'gst') selected @endif>GST</option>
                            <option value="non_gst" @if($type == 'non_gst') selected @endif>NON-GST</option>
                        </select>
                    </div>
                    <div class="col-4">
                        <input type="search" name="store_name" class="form-control select-md" id="store_name" placeholder="Search store by name" onkeyup="getStores(this.value);" value="{{$store_name}}" autocomplete="off">
                        <input type="hidden" name="store_id" id="store_id" value="{{$store_id}}">
                        <div class="respDropStore" id="respDropStore" style="position: relative;"></div>
                    </div>
                    <div class="col-auto">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <input type="search" id="term" name="term" class="form-control select-md" placeholder="Search invoice or slip" autocomplete="off" value="{{$term}}">
                            </div>                                
                            <input type="submit" hidden />
                        </div>
                    </div>
                </div>
            </form>
            </div>
            
            <div class="filter">
                <div class="row align-items-center justify-content-between">
                    <div class="col">

                    </div>
                    <div class="col-auto">                        
                        <p>{{$total}} Total invoices & Packing slips</p>                        
                    </div>
                </div>
            </div>
            <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>      
                        <th>#</th>    
                        <th>Date & Time</th>              
                        <th>Invoice No</th>    
                        <th>Packing Slip No</th>                
                        <th>Party/Store</th>
                        <th>Slip Amount</th>
                        <th>Tally Bill Upload</th>
                        <th>Transport LR Upload</th>
                        <th>Message Status</th>
                        <th>Action</th>
                        <th>Status</th>
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
                    @php
                    if ($item->tally_bill_file && $item->transport_lr_file) {
                        $status_color = 'green_class';
                    } elseif (
                        ($item->tb_required == 0 && $item->lr_required == 0) || 
                        ($item->tb_required == 0 && isset($item->transport_lr_file)) || 
                        ($item->lr_required == 0 && isset($item->tally_bill_file))
                    ) {
                        $status_color = 'yellow_class';
                    } else {
                        $status_color = 'red_class';
                    }

                    @endphp
                    <tr>        
                        <td>{{$i}}</td>   
                        <td>
                            <p class="m-0">{{date('d/m/Y H:i A', strtotime($item->created_at))}}
                            </p>
                            
                        </td>             
                        <td>
                            <a href="{{ route('admin.packingslip.view_invoice', $item->invoice_no) }}" class="btn btn-outline-secondary select-md">{{$item->invoice_no}}</a>
                        </td>
                        <td>
                            @if($item->packingslip)
                                <a href="{{ route('admin.packingslip.get_pdf',$item->packingslip->slipno) }}" class="btn btn-outline-secondary select-md">{{$item->packingslip->slipno}}</a>
                            @endif
                        </td>
                        <td>
                            <p class="small text-muted mb-1">
                                
                                @if (!empty($item->store->bussiness_name))
                                @php
                                    $bussiness_name = $item->store->bussiness_name;
                                @endphp
                                @else
                                    @php
                                        $bussiness_name = $item->store->store_name;
                                    @endphp
                                @endif
                                <span><strong>{{$bussiness_name}}</strong> </span>                        
                            </p>
                        </td>
                       
                        <td>Rs. {{ number_format((float)$item->net_price, 2, '.', '') }}</td>

                        <td> 
                            <input type="checkbox" class="tally_not_required" id="tally_not_required{{$item->id}}" data-id="{{$item->id}}" {{$item->tb_required==0?"checked":""}}> <label for="tally_not_required{{$item->id}}">Not Required</label>
                            <div id="hiddenTallyDiv{{$item->id}}" style="display:{{$item->tb_required==0?"none":""}}">
                            {{-- @if(empty($item->tally_bill_file)) --}}
                                <a href="#" class="btn btn-outline-secondary select-md" data-bs-toggle="modal" data-bs-target="#TallyModal{{$item->id}}">Upload File</a>
                            {{-- @endif --}}
                            </div>
                            <div id="hiddenTallyFile{{$item->id}}">
                                @if(!empty($item->tally_bill_file))
                                <a href="{{asset($item->tally_bill_file)}}" class="btn btn-secondary select-md" download>Tally Bill</a>
                                @endif
                            </div>
                            <!-- Modal -->
                            <div class="modal fade" id="TallyModal{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropTally{{$item->id}}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="staticBackdropTally{{$item->id}}">Tally Bill Upload (Photo/PDF)</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="uploadTallyForm{{$item->id}}" enctype="multipart/form-data">
                                                <div class="mb-3">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$item->id}}">
                                                    <input type="hidden" name="bussiness_name" value="{{$bussiness_name}}">
                                                    <label for="tally_file{{$item->id}}" class="form-label">Choose File</label>
                                                    <input type="file" class="form-control" id="tally_file{{$item->id}}" name="tally_file">
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <div id="tally_alert_message{{$item->id}}"></div>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-secondary" onclick="uploadTallyFile({{$item->id}})" id="uploadTallyButton{{$item->id}}">Upload</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td> 
                            <input type="checkbox" class="lr_not_required" id="lr_not_required{{$item->id}}" data-id="{{$item->id}}" {{$item->lr_required==0?"checked":""}}> <label for="lr_not_required{{$item->id}}">Not Required</label>
                            <div id="hiddenLRDiv{{$item->id}}" style="display:{{$item->lr_required==0?"none":""}}">
                                <a href="#" class="btn btn-outline-secondary select-md" data-bs-toggle="modal" data-bs-target="#TRansportLRModal{{$item->id}}">Upload File</a>
                                </div>
                            <div id="hiddenLRFile{{$item->id}}">
                                @if(!empty($item->transport_lr_file))
                                <a href="{{asset($item->transport_lr_file)}}" class="btn btn-secondary select-md" download>Transport LR</a>
                                @endif
                            </div>
                            <!-- Modal -->
                            <div class="modal fade" id="TRansportLRModal{{$item->id}}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLR{{$item->id}}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5" id="staticBackdropLR{{$item->id}}">Transport LR Upload (Photo/PDF)</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="uploadLRForm{{$item->id}}" enctype="multipart/form-data">
                                                <div class="mb-3">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$item->id}}">
                                                    <input type="hidden" name="bussiness_name" value="{{$bussiness_name}}">
                                                    <label for="lr_file{{$item->id}}" class="form-label">Choose File</label>
                                                    <input type="file" class="form-control" id="tally_file{{$item->id}}" name="lr_file">
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer">
                                            <div id="lr_alert_message{{$item->id}}"></div>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-secondary" onclick="uploadLRFile({{$item->id}})" id="uploadLRButton{{$item->id}}">Upload</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td id="counter_{{$item->id}}">
                            @if($item->status==1)
                                @if(LeftWhatsappCounter($item->id)=="Date Expired")
                                <span>Pending</span>
                                @else
                                 {{LeftWhatsappCounter($item->id)}}
                                @endif
                            @elseif($item->status==2)
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
                            @if($item->status==3)
                                {{-- <a href="#" class="btn select-md btn-outline-danger">Cancelled</a> --}}
                                <a href="#" onclick="WhatsAppActive({{$item->id}})" class="btn select-md btn-outline-primary">Active</a>
                            @else
                                <a href="#" onclick="WhatsAppCancel({{$item->id}})" class="btn select-md btn-outline-danger">Cancel</a>
                                @if($status_color!="red_class")
                                    @if($item->store->whatsapp)
                                    <a href="{{route('admin.whats-app.send_text_whatsapp_message', [$item->id,$item->store->whatsapp])}}" class="btn select-md btn-outline-success sendLedgerMessageBtn">Send</a>
                                    @else
                                    <a href="#" class="btn select-md btn-outline-secondary">No whatsapp</a>
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td class="{{$item->last_whatsapp?"green_class":$status_color}}">
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
        </div>        
            {{$data->links()}}
        </div>       
    </div>
</section>

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
    $(document).ready(function(){
        $('.tally_not_required').change(function(){
            var id = $(this).data('id');
            if($(this).is(':checked')){
                // Display a confirmation dialog
                if(confirm("Are you sure you want to not require the file?")){
                    // If user confirms, make the AJAX call
                    $.ajax({
                        url: "{{route('admin.whats-app.tally_bill_not_required')}}",
                        type: 'GET',
                        data: {id: id,status:0},
                        success: function(response) {
                            // If AJAX call is successful, hide the div
                            $('#hiddenTallyDiv'+id).hide();
                            $('#hiddenTallyFile'+id).hide();
                            setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            alert("Error occurred while processing your request.");
                            setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                        }
                    });
                } else {
                    // If user cancels the confirmation, keep the checkbox checked
                    $(this).prop('checked', true);
                }
            } else {
                $.ajax({
                    url: "{{route('admin.whats-app.tally_bill_not_required')}}",
                    type: 'GET',
                    data: {id: id, status:1},
                    success: function(response) {
                        $('#hiddenTallyDiv'+id).show();
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        alert("Error occurred while processing your request.");
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    }
                });
            }
        });
    });

    function WhatsAppCancel(id) {
        if (confirm("Are you sure you want to cancel?")) {
            window.location.href = "{{ route('admin.whats-app.invoice_cancel', ':id') }}".replace(':id', id);
        }
    }
    function WhatsAppActive(id) {
        if (confirm("Are you sure you want to active?")) {
            window.location.href = "{{ route('admin.whats-app.invoice_active', ':id') }}".replace(':id', id);
        }
    }
    $(document).ready(function(){
        $('.lr_not_required').change(function(){
            var id = $(this).data('id');
            if($(this).is(':checked')){
                // Display a confirmation dialog
                if(confirm("Are you sure you want to not require the file?")){
                    // If user confirms, make the AJAX call
                    $.ajax({
                        url: "{{route('admin.whats-app.lr_bill_not_required')}}",
                        type: 'GET',
                        data: {id: id,status:0},
                        success: function(response) {
                            // If AJAX call is successful, hide the div
                            $('#hiddenLRDiv'+id).hide();
                            $('#hiddenLRFile'+id).hide();
                            setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            alert("Error occurred while processing your request.");
                            setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                        }
                    });
                } else {
                    // If user cancels the confirmation, keep the checkbox checked
                    $(this).prop('checked', true);
                }
            } else {
                $.ajax({
                    url: "{{route('admin.whats-app.lr_bill_not_required')}}",
                    type: 'GET',
                    data: {id: id, status:1},
                    success: function(response) {
                        $('#hiddenLRDiv'+id).show();
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        alert("Error occurred while processing your request.");
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    }
                });
            }
        });
    });

    $(document).ready(function(){
        $('div.alert').delay(2000).slideUp(300);
    })
    
    $('#term').on('search', function () { 
        // $('#term').val('');       
        $('#searchForm').submit();
    });
    $('#store_name').on('search', function () {   
        // $('#store_name').val('');
        $('#store_id').val('');
        $('#searchForm').submit();
    });

    $('#type').on('change', function(){
        $('#searchForm').submit();
    });

    function getStores(val){
        if(val.length > 0){
            $.ajax({
                url: "{{ route('admin.ledger.getUsersByType') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: val,
                    type: 'store'
                },
                success: function(result) {

                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {                        
                            if(value.bussiness_name != ''){
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.bussiness_name}')">${value.bussiness_name}</a>`;
                            } else {
                                content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCode(${value.id},'${value.name}')">${value.name}</a>`;
                            }                        
                            
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 user-dropdown" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No store found</li></div>`;
                    }
                    $('.respDropStore').html(content);
                }
            });
        } else {
            $('.respDropStore').text('');
            $('#store_id').val(0);
            $('#store_name').val('');
        }
        
    }

    function fetchCode(id,name) {
        $('.user-dropdown').hide()
        $('input[name="store_id"]').val(id)
        $('input[name="store_name"]').val(name)
        $('#searchForm').submit();
    }
    
</script>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.3/jquery.validate.min.js"></script>
<script>
    $(document).ready(function(){
        // Add custom validation method for file extension
        $.validator.addMethod("extension", function(value, element, param) {
            param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif|pdf";
            return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
        }, "Please select a valid file type.");

        // Initialize form validation
        $("form[id^='uploadTallyForm']").each(function() {
            $(this).validate({
                rules: {
                    tally_file: {
                        required: true,
                        extension: "pdf|png|jpg|jpeg"
                    }
                },
                messages: {
                    tally_file: {
                        required: "Please select a file.",
                        extension: "Please select a valid PDF, PNG, JPG, JPEG, file."
                    }
                },
                submitHandler: function(form) {
                    // Handle form submission
                    var formData = new FormData(form);
                    var id = $(form).find('input[name="id"]').val();
                    $('#uploadTallyButton' + id).prop('disabled', true);
                    // Example: You can use AJAX to submit the form data
                    $.ajax({
                        url: "{{route('admin.whats-app.upload_tally_bill')}}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            var message = ''; // Initialize empty message
                            if (response.status == 200) {
                                message = '<div class="alert alert-success" role="alert">File uploaded successfully</div>';
                            } else {
                                message = '<div class="alert alert-danger" role="alert">Something went wrong! Please try again.</div>';
                            }
                            $('#tally_alert_message'+id).append(message);
                            // Set a timeout to remove the message after 5 seconds
                            setTimeout(function() {
                                $('.alert').remove();
                                window.location.reload();
                            }, 2000);
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            var Message_error = xhr.responseText;
                            var errorMessage = '<div class="alert alert-danger" role="alert">'+Message_error+'</div>';
                            $('#tally_alert_message'+id).append(errorMessage);
                            setTimeout(function() {
                                $('.alert').remove();
                                window.location.reload();
                            }, 2000);
                        }

                    });
                }
            });
        });
    });
    // Function to trigger form submission
    function uploadTallyFile(itemId) {
        $('#uploadTallyForm' + itemId).submit();
    }
    $(document).ready(function(){
        // Add custom validation method for file extension
        $.validator.addMethod("extension", function(value, element, param) {
            param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif|pdf";
            return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
        }, "Please select a valid file type.");

        // Initialize form validation
        $("form[id^='uploadLRForm']").each(function() {
            $(this).validate({
                rules: {
                    lr_file: {
                        required: true,
                        extension: "pdf|png|jpg|jpeg"
                    }
                },
                messages: {
                    tally_file: {
                        required: "Please select a file.",
                        extension: "Please select a valid PDF, PNG, JPG, JPEG, file."
                    }
                },
                submitHandler: function(form) {
                    // Handle form submission
                    var formData = new FormData(form);
                    var id = $(form).find('input[name="id"]').val();
                    $('#uploadLRButton' + id).prop('disabled', true);
                    // Example: You can use AJAX to submit the form data
                    $.ajax({
                        url: "{{route('admin.whats-app.upload_lr_bill')}}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            var message = ''; // Initialize empty message
                            if (response.status == 200) {
                                message = '<div class="alert alert-success" role="alert">File uploaded successfully</div>';
                            } else {
                                message = '<div class="alert alert-danger" role="alert">Something went wrong! Please try again.</div>';
                            }
                            $('#lr_alert_message'+id).append(message);
                            // Set a timeout to remove the message after 5 seconds
                            setTimeout(function() {
                                $('.alert').remove();
                                window.location.reload();
                            }, 2000);
                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            var Message_error = xhr.responseText;
                            var errorMessage = '<div class="alert alert-danger" role="alert">'+Message_error+'</div>';
                            $('#lr_alert_message'+id).append(errorMessage);
                            setTimeout(function() {
                                $('.alert').remove();
                                window.location.reload();
                            }, 2000);
                        }

                    });
                }
            });
        });
    });
    // Function to trigger form submission
    function uploadLRFile(itemId) {
        $('#uploadLRForm' + itemId).submit();
    }
    $(document).ready(function() {
        function updateContent() {
            @php 
                if (count($data) > 0){
                    foreach($data as $key => $item){
                        if($item->status==1) {
                            echo 'var itemId_' . $key . ' = ' . json_encode($item->id) . ';';
                            echo '$.get("../left-whatsapp-counter/" + itemId_' . $key . ', function(response) {';
                            echo '    if (response.counter == "Date Expired") {';
                            echo '        document.getElementById("counter_" + itemId_' . $key . ').innerHTML = "<span>Pending</span>";';
                            echo '    } else {';
                            echo '        document.getElementById("counter_" + itemId_' . $key . ').innerHTML = response.counter;';
                            echo '    }';
                            echo '});';
                        } elseif($item->status == 2) {
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