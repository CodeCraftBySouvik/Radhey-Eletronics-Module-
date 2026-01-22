@extends('admin.layouts.app')
@section('page', 'View GRN')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li><a href="{{ route('admin.purchaseorder.index') }}?type=grn">GRN</a></li>
        <li>View GRN</li>
    </ul>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                                        
                    @if($errors->any())            
                        {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
                    @endif
                    @if (Session::has('message'))
                         <div class="alert alert-success" role="alert">
                            {{ Session::get('message') }}
                        </div>
                    @endif
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th colspan="">No of Ctns</th>
                                <th>Barcode per Ctn</th>                                
                                <th>Weight per Ctn</th>
                                <th>Scanned Weight</th>
                                <th>Pcs per Ctn</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i=1;
                            @endphp
                            @forelse ($products as $item)                            
                            <tr>
                                <input type="hidden" name="" value="{{ $item->product_id }}">
                                <td rowspan="{{ count($item->boxes) }}">{{$i}}</td>
                                <td rowspan="{{ count($item->boxes) }}">
                                    <label class="label-control">
                                        {{$item->pro_name}}  
                                    </label>
                                </td>
                                <td rowspan="{{ count($item->boxes) }}">                                    
                                    <span>{{ count($item->boxes)  }}</span>
                                </td>
                                
                                @forelse ($item->boxes as $box)
                                  
                                
                                <td>
                                    <span title="{{$box->barcode_no}}">
                                        {!! $box->code_html !!}
                                        <span style="width: 100%; display: block; text-align: center; color: #000000;">{{$box->barcode_no}}</span>
                                    </span>                                   
                                </td>                                
                                <td>
                                    {{$item->weight}} {{$item->weight_unit}}
                                </td>
                                <td>
                                    <span id="scannedweight{{$box->barcode_no}}">{{$box->scanned_weight_val / 1000}} kg</span>
                                </td>
                                <td>
                                    {{$box->pcs}} pieces
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
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
                        
    })

    

    function downloadImage(name){
        var url = "https://bwipjs-api.metafloor.com/?bcid=code128&text="+name;

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
