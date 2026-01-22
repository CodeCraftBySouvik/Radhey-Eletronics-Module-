@extends('admin.layouts.app')
@section('page', 'Generate GRN')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li><a href="{{ route('admin.purchaseorder.index') }}">PO</a></li>
        <li><a href="{{route('admin.purchaseorder.view',$id)}}">{{$unique_id}}</a></li>
        <li>Generate GRN</li>
    </ul>
    
    @if (empty($goods_in_type))
    <div class="row">
        <form action="" method="GET">  
        <div class="row">
            <div class="col-md-4">
                <div class="form-group d-flex align-items-center" style="height: 30px;">
                    <label for="" class="me-2">Goods In With  </label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input choose-type" type="radio" name="goods_in_type" id="scan" value="scan">
                        <label class="form-check-label" for="scan">Scan</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input choose-type" type="radio" name="goods_in_type" id="bulk" value="bulk">
                        <label class="form-check-label" for="bulk">Bulk</label>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex flex-row">
                    <a href="{{ route('admin.purchaseorder.index') }}" class="btn btn-danger select-md me-2">Back</a>
                    <button type="submit" class="btn btn-success select-md" id="nextBtn">Next</button>
                </div>
            </div>
        </div>          
       
        </form>
    </div>
    @endif

    @if(!empty($goods_in_type))
    <div class="search__filter">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                @if ($goods_in_type == 'bulk')
                <span class="badge bg-success">
                    BULK GOODS IN
                </span> 
                @else
                <span class="badge bg-success">
                    SCAN GOODS IN
                </span> 
                @endif
                    
            </div>
            <div class="col-auto">
                    
            </div>
            <div class="col-auto">
                <form action="" id="searchForm">
                    <input type="hidden" name="goods_in_type" value="{{$goods_in_type}}">
                <div class="row g-3 align-items-center">
                    <div class="col-auto">
                        <a href="{{ route('admin.barcodes',$id) }}" class="btn btn-outline-primary select-md">Download Barcodes </a>
                    </div>
                    <div class="col-auto">
                        <input type="search" name="search" value="{{$search}}" class="form-control select-md" placeholder="Search items..">
                    </div>
                    <div class="col-auto">
                        
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form id="myForm" method="POST" action="{{ route('admin.purchaseorder.saveinventory') }}">
                    @csrf  
                    <input type="hidden" name="unique_id" value="{{$unique_id}}">          
                    <input type="hidden" name="" id="total_checked" value="{{$total_checked}}">
                    <input type="hidden" name="id" value="{{$id}}">
                    <input type="hidden" name="goods_in_type" value="{{$goods_in_type}}">
                    
                    @if($errors->any())            
                        {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
                    @endif
                    @if (Session::has('message'))
                         <div class="alert alert-success" role="alert">
                            {{ Session::get('message') }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    @if ($goods_in_type == 'bulk')
                                    <th>Bulk In</th>
                                    @endif
                                    <th>#</th>
                                    <th>Product</th>
                                    <th colspan="2">No of Ctns</th>
                                    
                                    <th>Barcode per Ctn</th>                                
                                    <th>Weight per Ctn</th>
                                    @if ($goods_in_type == 'scan')                                    
                                    <th>Scanned Weight</th>
                                    @endif
                                    <th>Pcs per Ctn</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i=1;
                                @endphp
                                @forelse ($data as $product_id => $barcodes) 
                                @php
                                    // dd($item);
                                    $pro_name = getSingleAttributeTable('products',$product_id,'name');
                                    $getPODetails = getPODetails($id,$product_id);
                                    $weight = $getPODetails->weight;
                                    $weight_unit = $getPODetails->weight_unit;
                                    
                                    $unit_price = $getPODetails->unit_price;
                                    $piece_price = $getPODetails->piece_price;
                                
                                    $isBulkScanned = isBulkScanned($id,$product_id);
                                    $getCountPOGRN = getCountPOGRN($id,$product_id);
                                @endphp  
                                <input type="hidden" name="products[{{$i}}][product_id]" value="{{$product_id}}">    
                                <input type="hidden" name="products[{{$i}}][unit_price]" value="{{$unit_price}}">    
                                <input type="hidden" name="products[{{$i}}][piece_price]" value="{{$piece_price}}"> 
                                @if ($goods_in_type == 'bulk')
                                <input type="hidden" name="products[{{$i}}][count_scanned]" value="{{ count($barcodes) }}" id=""> 
                                @else
                                <input type="hidden" name="products[{{$i}}][count_scanned]" value="{{$getCountPOGRN}}" id="count_scanned{{$product_id}}">   
                                @endif
                                
                                                
                                <tr>
                                    @if ($goods_in_type == 'bulk')
                                    <td class="check-column" rowspan="{{ count($barcodes) }}">
                                        <div class="form-check">
                                            
                                                <input class="form-check-input data-check-{{$product_id}}" name=""  type="checkbox" value="1" id="bulk_scan_{{$product_id}}"  onchange="bulkScan({{$product_id}});"   @if($isBulkScanned) checked  @endif  style="height: 15px; width: 15px; border-radius: 3px;">
                                        </div>
                                    </td>
                                    @endif
                                    <td rowspan="{{ count($barcodes) }}">{{$i}}</td>
                                    <td rowspan="{{ count($barcodes) }}">
                                        <label class="label-control">
                                            {{$pro_name}}  
                                        </label>
                                        <br/>
                                        
                                        
                                        {{-- <input type="hidden" name="quantity[{{$product_id}}]"  value="{{ count($barcodes) }}"> --}}
                                    </td>
                                    <td rowspan="{{ count($barcodes) }}">                                    
                                        <span>{{ count($barcodes)  }}</span>
                                    </td>
                                    
                                    @forelse ($barcodes as $box)
                                    
                                    <td class="check-column">
                                        <div class="form-check">
                                            @php
                                                $data_bulkable = "data-bulkable-".$product_id;
                                            @endphp
                                            <input class="form-check-input data-barcode {{$data_bulkable}}  data-check-{{$product_id}}" name="barcode_no[]" value="{{$box->barcode_no}}" type="checkbox" value="1" id="barcode_no_{{$box->barcode_no}}" onclick="return false" @if(!empty($box->is_scanned) || !empty($box->is_bulk_scanned)) checked @endif >
                                            
                                        </div>
                                    </td>
                                    <td>
                                        <span title="{{$box->barcode_no}}">
                                            {!! $box->code_html !!}
                                            <span style="width: 100%; display: block; text-align: center; color: #000000;">{{$box->barcode_no}}</span>
                                        </span>                                   
                                    </td>                                
                                    <td>
                                        {{$weight}} {{$weight_unit}}
                                    </td>
                                    @if ($goods_in_type == 'scan')
                                    <td>
                                        <span id="scannedweight{{$box->barcode_no}}">
                                            @if (!empty($box->scanned_weight_val))
                                                {{$box->scanned_weight_val / 1000}} kg
                                            @endif
                                            
                                        </span>
                                    </td>
                                    @endif
                                    
                                    <td>
                                        {{$box->pcs}} pieces
                                    </td>
                                    {{-- <input type="hidden" name="pcs[]" value="{{$box->pcs}}"> --}}
                                    <td>
                                        
                                        <label for="barcode_no_{{$box->barcode_no}}" id="barcode_label_{{$box->barcode_no}}" class="text-center d-block pt-2 fw-bold label-barcode-{{$product_id}}">
                                            @if (!empty($box->is_scanned))
                                                SCANNED
                                            @elseif (!empty($box->is_bulk_scanned))
                                                NA
                                            @else
                                                YET TO SCAN
                                            @endif
                                        </label>
                                        
                                        @if (!empty($box->is_new))
                                            <span class="badge bg-dark">New</span>
                                        @endif                                     
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-success select-md" onclick="downloadImage('{{$box->barcode_no}}')" >DOWNLOAD</button>
                                        
                                        @if ($goods_in_type == 'scan')
                                            @if (empty($box->is_scanned))
                                                <a href="{{ route('admin.purchaseorder.archiveBox',[$id,$product_id,$box->barcode_no,Request::getQueryString()]) }}" onclick="return confirm('Are you sure want to archive box?');" class="btn btn-warning select-md" id="archivebtn{{$box->barcode_no}}">Archive</a>
                                            @endif
                                        @endif
                                        
                                    </td>
                                </tr> 
                                
                                @empty
                                <tr></tr>
                                @endforelse
                                @php
                                    $i++;
                                @endphp   
                                @empty
                                    
                                @endforelse
                            <tbody>
                        </table> 
                    </div>
                    <div class="form-group">
                        <a href="{{ route('admin.purchaseorder.index') }}" class="btn btn-sm btn-danger">Back</a>
                        <button id="submitBtn" type="submit" class="btn btn-sm btn-success">Generate</button>

                        <br/>
                        @if ($goods_in_type == 'scan')
                        <span>** Currently scanned total <strong id="total_checked_span">{{$total_checked}}</strong> boxes  </span>
                        @endif
                        
                        <br/>
                        @if (!empty($search))
                        <span>Please clear search data to preceed</span>                            
                        @endif

                        @error('duplicate_barcode_err_msg') <p class="small text-danger">{{ $message }}</p> @enderror  

                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
    
</section>
<script>
    var search = "{{$search}}";
    var id = "{{$id}}";
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
        $('#nextBtn').attr('disabled',true);
        var goods_in_type = "{{ $goods_in_type }}";

        $('.choose-type').on('click', function(){
            $('#nextBtn').attr('disabled',false);
        })


        var total_checkbox = $('input:checkbox.data-barcode').length;
        var total_checked = $('input:checkbox.data-barcode:checked').length;

        console.log('total_checkbox:- '+total_checkbox);
        console.log('total_checked:- '+total_checked);

        if(goods_in_type != '' && goods_in_type == 'scan'){
            
            $('#submitBtn').prop('disabled', false);
            const interval = setInterval(() => {        
                getScannedImages(id);
            }, 5000);
            
        } else {
            if(total_checked == total_checkbox){
                if(search == ''){
                    $('#submitBtn').prop('disabled', false);
                } else {
                    $('#submitBtn').prop('disabled', true);
                }  
            }else{
                $('#submitBtn').prop('disabled', true);                
            }
        }
                        
    });

    $("#myForm").submit(function() {        
        $('#submitBtn').prop('disabled', true);         
    });

    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#searchForm').submit();
    });

    function bulkScan(product_id){
        var is_scanned = 0;
        var is_bulk_scanned = 0;
        if (document.getElementById('bulk_scan_'+product_id).checked == true) {
            
            var box = confirm("Are you sure want to bulk in?");
            // alert(box);
            if (box == true)  {
                $('input:checkbox.data-bulkable-'+product_id).prop('checked', true);
                $('input:checkbox#bulk_scan_'+product_id).prop('checked', true);
                is_scanned = 1;
                is_bulk_scanned = 1;
                console.log("AA !!!")
            }  else  {
                $('input:checkbox.data-bulkable-'+product_id).prop('checked', false);
                document.getElementById('bulk_scan_'+product_id).checked = false;        
                console.log("BB !!!")        
            }
       
        } else {
            $('input:checkbox.data-bulkable-'+product_id).prop('checked', false);
            is_scanned = 0;
            is_bulk_scanned = 0;   
            // console.log("DD !!!")         
        }

        $.ajax({
            url: "{{ route('admin.purchaseorder.pobulkscan') }}",
            dataType: 'json',
            type: 'post',
            data: {
                "_token": "{{ csrf_token() }}",
                "purchase_order_id": id,
                "product_id": product_id,
                "is_bulk_scanned": is_bulk_scanned,
                "is_scanned": is_scanned
            },
            success: function(data){                
                var sucessData = data; 
                if(is_scanned == 0){
                    $('.label-barcode-'+product_id).html('YET TO SCAN');
                    $('#data-bulkable-'+product_id).prop('checked', false);
                    $('input:checkbox#bulk_scan_'+product_id).prop('checked', false);
                    var total_barcodes = $('input:checkbox.data-barcode').length;
                    var total_scanned = $('input:checkbox.data-barcode:checked').length;
                    
                } else if(is_scanned == 1){
                    // alert('All scanned');
                    $('.label-barcode-'+product_id).html('NA');
                    $('#data-bulkable-'+product_id).prop('checked', true);
                    $('input:checkbox#bulk_scan_'+product_id).prop('checked', true);
                    var total_barcodes = $('input:checkbox.data-barcode').length;
                    var total_scanned = $('input:checkbox.data-barcode:checked').length;
                }            
            }
        });
        var total_barcodes = $('input:checkbox.data-barcode').length;
        var total_scanned = $('input:checkbox.data-barcode:checked').length;
        if(total_scanned == total_barcodes) {
            if(search == ''){
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#submitBtn').prop('disabled', true);
            }
            
        } else if (total_scanned < total_barcodes) {
            $('#submitBtn').prop('disabled', true);
        }
    }

    function getScannedImages(id){
        $.ajax({
            url: "{{ route('admin.purchaseorder.checkScannedboxes') }}",
            dataType: 'json',
            type: 'post',
            data: {
                "_token": "{{ csrf_token() }}",
                "id": id
            },
            success: function(data){
                var resp = data.successData;
                var countScanned = data.count_pro_scanned;
                var sucessData = resp;
                console.log(countScanned)
                
                // console.log(sucessData);
                for(var i = 0; i < sucessData.length; i++) {
                    console.log('barcode_no:- '+sucessData[i].barcode_no)
                    var scanned_weight_val = (sucessData[i].scanned_weight_val / 1000);
                    $('#barcode_label_'+sucessData[i].barcode_no).text('SCANNED');
                    $('#scannedweight'+sucessData[i].barcode_no).text(scanned_weight_val+' kg');
                    
                    $('#archivebtn'+sucessData[i].barcode_no).hide();
                    $('#barcode_no_'+sucessData[i].barcode_no).attr('checked', 'checked');

                    var total_checkbox = $('input:checkbox.data-barcode').length;
                    var total_checked = $('input:checkbox.data-barcode:checked').length;

                    $('#total_checked').val(sucessData.length);
                    var new_total_checked = $('#total_checked').val();
                    $('#total_checked_span').text(new_total_checked);

                    if(total_checkbox == new_total_checked){
                        if(search == ''){
                            $('#submitBtn').prop('disabled', false);
                        } else {
                            $('#submitBtn').prop('disabled', true);
                        }
                    }

                }

                for(var i = 0; i < countScanned.length; i++) {
                    console.log('product_id:- '+countScanned[i].product_id)
                    $('#count_scanned'+countScanned[i].product_id).val(countScanned[i].total_scanned);
                    console.log('total_scanned:- '+countScanned[i].total_scanned)
                }
               
            }
        });
    }

    function downloadImage(name){
        var url = "https://bwipjs-api.metafloor.com/?bcid=code128&includetext&text="+name;

        fetch(url)
            .then(resp => resp.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                // the filename you want
                a.download = name;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
            })
            .catch(() => alert('An error sorry'));
    }
</script>
@endsection
