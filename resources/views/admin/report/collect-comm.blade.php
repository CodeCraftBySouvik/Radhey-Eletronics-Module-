@extends('admin.layouts.app')
@section('page', 'Salesman Collection Commission')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Report</li>
        <li>Salesman Collection Commission</li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif  
        
    
    <div class="row">
        <div class="col-12" id="cityDiv">
            <form action="" id="searchForm">
            <div class="row g-3 align-items-end">                
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">Month     </label> 
                        <input type="month" name="month" class="form-control select-md" max="{{ date('Y-m') }}" id="month" value="{{ $month }}" >
                    </div>
                </div> 
                <div class="col-sm-2">
                    <div class="form-group">
                        <label for="">Salesman  </label> 
                        <select name="user_id" id="user_id" class="form-control select-md">
                            <option value="" hidden selected>Select one</option>
                            <option value="">All</option>
                            @forelse ($salesman as $user)
                                <option value="{{$user->id}}" @if($user_id == $user->id) selected @endif>{{$user->name}}</option>
                            @empty
                                
                            @endforelse
                        </select>
                    </div>
                </div>                 
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-success select-md">Submit</button>
                    <a href="{{ route('admin.report.staff-commission') }}" class="btn btn-warning select-md">Reset</a>
                </div>
            </div>            
            </form>   
        </div>      
    </div>
    <div class="row" id="commDiv">
        <div class="col-ls-12">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Month</th>
                            <th>Salesman</th>
                            <th>Commission</th>
                            <th>Commission On</th>
                            <th>Commission Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i=1;
                        @endphp
                        @foreach ($data as $item)
                        @php
                            
                            // dd($collection_cities);
                        @endphp
                        <tr>
                            <td>{{$i}}</td>
                            <td>
                                {{ $item->unique_id }}
                            </td>
                            <td>
                                {{ date('M Y', strtotime($item->year_val.'-'.$item->month_val)) }}
                            </td>
                            <td>
                                {{ $item->name }}
                            </td>                            
                            <td>
                                {{ $item->targeted_collection_amount_commission }} %
                            </td>
                            <td>
                                Rs. {{ number_format((float)$item->commission_on_amount, 2, '.', '') }}
                            </td>
                            <td>
                                Rs. {{ number_format((float)$item->final_commission_amount, 2, '.', '') }}
                            </td>
                            <td>
                                <a href="{{ route('admin.report.monthly-commissionable-collections', [$item->user_id,$item->month_val,$item->year_val]) }}" class="btn btn-outline-success select-md">Download Report</a>
                            </td>
                        </tr>

                        @php
                            $i++;
                        @endphp
                        @endforeach
                        
                    </tbody>
                </table>
            </div>
        </div>      
    </div>   
</section>
<script>
    function getBrowserType() {
        const test = regexp => {
            return regexp.test(navigator.userAgent);
        };
        
        var navigator_useragent = navigator.userAgent;
        console.log(navigator_useragent);
        if (test(/opr\//i) || !!window.opr) {
            return 'Opera';
        } else if (test(/edg/i)) {
            return 'Microsoft Edge';
        } else if (test(/chrome|chromium|crios/i)) {
            return 'Google Chrome';
        } else if (test(/firefox|fxios/i)) {
            return 'Mozilla Firefox';
        } else if (test(/safari/i)) {
            return 'Apple Safari';
        } else if (test(/trident/i)) {
            return 'Microsoft Internet Explorer';
        } else if (test(/ucbrowser/i)) {
            return 'UC Browser';
        } else if (test(/samsungbrowser/i)) {
            return 'Samsung Browser';
        } else {
            return 'Unknown browser';
        }
    }
    const browserType = getBrowserType();
    console.log(browserType);
    


    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);

        if(browserType == 'Mozilla Firefox' || browserType == 'Microsoft Internet Explorer' || browserType == 'Apple Safari'){
            $('#month').attr('readonly', true);
            alert('For filtering month please use Chrome. '+browserType+' does not allow input type month');
        }
    });

    

    $('input[type=search]').on('search', function () {
        // search logic here
        // this function will be executed on click of X (clear button)
        $('#city_id').val('');
        $('#searchForm').submit();
    });

    function getCityByName(name){
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.ledger.searchCities') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    search: name
                },
                success: function(result) {
                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show city-dropdown select-md" aria-labelledby="dropdownMenuButton" style="width: 491px;">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCity(${value.id},'${value.name}')">${value.name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 city-dropdown select-md" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No city found</li></div>`;
                    }
                    $('#respDropCity').html(content);
                }
            });
        }   else {
            $('.city-dropdown').hide()
        } 
    }

    function fetchCity(id,name){        
        
        $('.city-dropdown').hide();
        $('#city_id').val(id);
        $('#city_name').val(name);
        
    }

</script>
@endsection