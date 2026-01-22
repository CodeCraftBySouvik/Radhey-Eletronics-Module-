@extends('admin.layouts.app')
@section('page', 'Search Stock Barcodes')
@section('content')
<style>
    .barcode_image{
        height: unset;
    }
</style>
<section>   
    <ul class="breadcrumb_menu">
        <li>Purchase Order</li>
        <li>Search Stock Barcodes</li>
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
                
            </div>
            <div class="col-md-6">
                <form action="" id="searchForm">
                <div class="row">
                    <div class="col-12 col-md mb-2">
                        <input type="search" name="search_product_name" value="{{$search_product_name}}" placeholder="Search product by name ..." class="form-control select-md"  id="searchProText" onkeyup="getProductByName(this.value);" autocomplete="off" > 
                        <input type="hidden" name="search_product_id" id="searchProId" value="{{$search_product_id}}">
                        <div class="respDrop" id="respDrop"></div>
                    </div>
                    <div class="col-12 col-md-auto mb-2">
                        <input type="search" name="search_barcode" value="{{$search_barcode}}" placeholder="Search barcode no ..." class="form-control select-md"  id="search_barcode" autocomplete="off">
                    </div>
                    
                    <div class="col-12 col-md-auto mb-2">
                        <input type="submit" title="Search" class="btn btn-outline-success select-md" >
                        <a href="{{ route('admin.grn.searchbarcodes') }}" class="btn btn-outline-danger select-md">Reset</a>
                        @if($countData>0)
                        <a id="printResultHandler" class="btn btn-outline-success select-md">Download</a>
                        @endif
                    </div>
                   
                </div>
                </form>
            </div>
        </div>
    </div>
    <div class="filter">
        <div class="row align-items-center justify-content-between">                
            <div class="col"></div>
            <div class="col-auto">                    
                <p>{{$countData}} Total Barcodes</p>
            </div>
        </div>
    </div>
    <div id="print_div">
        <div>
            <div class="row">
                @forelse ($data as $item)
                <div class="col-12 page_bar">
                    <div class="barcode_image" style="margin: 0 auto 4px">
                        <span title="{{$item->barcode_no}}">
                            {{-- {!! $item->code_html !!} --}}
                            <img class="" alt="Barcoded value {{$item->barcode_no}}" src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{$item->barcode_no}}&height=5&textsize=14&scale=3&includetext">
                                <span>{{$search_product_name}}</span>
                            {{-- <span style="width: 100%; display: block; text-align: center; color: #000000;">{{$item->barcode_no}}</span> --}}
                            {{-- <span style="width: 100%; display: block; text-align: center; color: #000000;">{{$search_product_name}}</span> --}}
                        </span>   
                        {{-- <button onclick="downloadImage('{{$item->barcode_no}}', '{{$search_product_name}}')" class="btn btn-outline-success select-md">Download Barcode</button>   --}}
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <p>No barcode found</p>
                </div>
                @endforelse
                
            </div>
        </div>
    </div>
   
</section>
@endsection
@section('script')
<script type="text/javascript">
    $(document).ready(function(){
        var searchProId = $('#searchProId').val();
        if(searchProId == ''){
            $('#search_barcode').prop('readonly', true);
        } else {
            $('#search_barcode').prop('readonly', false);
        }
        
    })
    $('input[name=search_product_name]').on('search', function () {
        
        $('#searchProId').val('');
        $('#searchForm').submit();
    });
    $('input[name=search_barcode]').on('search', function () {
        
        $('#searchForm').submit();
    });

    function getProductByName(name) {  
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.product.searchByName') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    term: name
                },
                success: function(result) {
                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show w-100 product-dropdown select-md" aria-labelledby="dropdownMenuButton">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchProduct(${value.id})">${value.name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 product-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No product found</li></div>`;
                    }
                    $('#respDrop').html(content);
                }
            });
        }   else {
            $('.product-dropdown').hide()
        }   
        
    }

    function fetchProduct(id) {
        $('.product-dropdown').hide()
        $.ajax({
            url: "{{ route('admin.product.viewDetail') }}",
            method: 'post',
            data: {
                '_token': '{{ csrf_token() }}',
                id: id
            },
            success: function(result) {
                console.log(result);
                var name = result.name;
                $('#searchProId').val(id);
                $('#searchProText').val(name);
                $('#searchForm').submit();
            }
        });                
    }

    function downloadImage(name, product) {
        var url = "https://bwipjs-api.metafloor.com/?bcid=code128&includetext&text=" + name;
        fetch(url)
            .then(resp => resp.blob())
            .then(blob => {
                const reader = new FileReader();
                reader.readAsDataURL(blob);
                reader.onloadend = function () {
                const base64data = reader.result;
                const img = new Image();
                img.src = base64data;
                img.onload = function () {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = img.width;
                    
                    // Measure the width of the product text
                    const textWidth = ctx.measureText(product).width;
                    
                    // Calculate the number of lines needed based on the text width
                    const linesNeeded = Math.ceil(textWidth / img.width);

                    // Calculate the height of the canvas based on the number of lines needed
                    canvas.height = img.height + linesNeeded * 40; // Assuming 20px for each line
                    
                    ctx.drawImage(img, 0, 0);
                    ctx.font = 'bold 13px Arial, Helvetica, sans-serif';
                    ctx.textAlign = 'center';
                    // Wrap text to the next line if it exceeds image width
                    wrapText(ctx, product, 115, img.height + 15, img.width, 20);

                    canvas.toBlob(function (blob) {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = name + ".png";
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                    });
                }
            }
            })
            .catch(() => alert('An error occurred.'));
    }

    function wrapText(context, text, x, y, maxWidth, lineHeight) {
        var words = text.split(' ');
        var line = '';

        for (var i = 0; i < words.length; i++) {
            var testLine = line + words[i] + ' ';
            var metrics = context.measureText(testLine);
            var testWidth = metrics.width;
            if (testWidth > maxWidth && i > 0) {
                context.fillText(line, x, y);
                line = words[i] + ' ';
                y += lineHeight;
            } else {
                line = testLine;
            }
        }
        context.fillText(line, x, y);
    }

    $(document).ready(function() {
        $("#printResultHandler").click(function() {

            //Get the HTML of div

            var print_header = '';

            var divElements = document.getElementById("print_div").innerHTML;

            var print_footer = '';



            //Get the HTML of whole page

            var oldPage = document.body.innerHTML;

            //Reset the page's HTML with div's HTML only

            document.body.innerHTML =

                    "<html><head><title></title></head><body><font size='2'>" +

                    divElements + "</font>" + print_footer + "</body>";

            //Print Page

            window.print();

            //Restore orignal HTML

            document.body.innerHTML = oldPage;

            //bindUnbind();

        });
    });

</script>
@endsection