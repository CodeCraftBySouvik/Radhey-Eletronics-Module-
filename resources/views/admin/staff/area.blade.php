@extends('admin.layouts.app')
@section('page', 'Cities & Commission')
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Staff</li>
        <li>{{ $user->name }}</li>
        <li>Cities & Commission</li>
    </ul> 
    @if (Session::has('message'))
    <div class="alert alert-success" role="alert">
        {{ Session::get('message') }}
    </div>
    @endif
    <form action="{{ route('admin.staff.save_areas') }}" id="" method="POST">
        @csrf
        <input type="hidden" name="user_id" value="{{$id}}">
        <div class="row">
            <div class="col-12" id="cityDiv">
                <h6>Assign Cities</h6>
                <div class="row g-3 align-items-end">
                    <div class="col-sm-2">
                        <div class="form-group">
                            <label for="">State</label>
                            <select name="parent_id" class="form-control" id="state_id">
                                <option value="">State</option>
                                @foreach ($states as $state)
                                <option value="{{ $state['id'] }}">{{ $state['name'] }}</option>
                                @endforeach
                                
                            </select>
                        </div>
                        
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="">Cities </label> 
                            <input type="text" name="" placeholder="Please Search Store" class="form-control  mb-0" value="" id="searchCityText" onkeyup="searchCities(this.value);">   
                        </div>
                        <div class="respDropCity" id="respDropCity"></div>
                    </div> 
                    @error('city_ids') <p class="small text-danger">{{ $message }}</p> @enderror                  
                </div>
                <div class="row ">
                    <ul class="stores_class">
                        
                        @if(!empty($user_cities))
                            @forelse ( $user_cities as $city )
                            <li id="cityli_{{$city['city_id']}}"> 
                                {{ $city['city']['name'] }}
                                {{-- {{getSingleAttributeTable('stores',$stores,'bussiness_name')}}  --}}
                                <a href="javascript:void(0);" onclick="removeCity({{$city['city_id']}});"><i class="fa fa-close"></i>
                                </a> 
                                <input type="hidden" class="city_ids" name="city_ids[]" value="{{$city['city_id']}}" >
                            </li>
                            @empty
                                
                            @endforelse 
                        @endif
                        
                    </ul>                
                </div>
                <div class="row">
                    <h6>Commission Criteria</h6>
                    {{-- <div class="col-auto">
                        <label for="">Minimum Monthly Collection Value For Commission Eligiblity <span class="text-danger">*</span> </label>
                        <div class="input-group col-auto">
                            <div class="input-group-prepend">
                                <a class="btn btn-outline-secondary">
                                    Rs. 
                                </a>                                        
                            </div>
                            <input type="text" maxlength="10" name="monthly_collection_target_value" id="" placeholder="Enter amount value" class="form-control" value="{{ (old('monthly_collection_target_value')) ? old('monthly_collection_target_value') : $user->monthly_collection_target_value }}" onkeypress="validateNum(event)">
                            
                        </div>
                        @error('monthly_collection_target_value') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div> --}}
                    <div class="col-auto">
                        <label for="">Targeted Collection Commission <span class="text-danger">*</span> </label>
                        <div class="input-group col-auto">
                            <input type="text" maxlength="5" name="targeted_collection_amount_commission" id="" placeholder="Enter percentage value" class="form-control" value="{{ (old('targeted_collection_amount_commission')) ? old('targeted_collection_amount_commission') :$user->targeted_collection_amount_commission }}" onkeypress="validateNum(event)">
                            <div class="input-group-append">
                                <a class="btn btn-outline-secondary">
                                    <i class="fa fa-percentage"></i>
                                </a>                                        
                            </div>
                           
                        </div>
                        @error('targeted_collection_amount_commission') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-auto ms-auto">
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-danger ">Back</a>
                        <button type="submit" class="btn btn-success ">Submit</button>
                    </div>
                </div>
            </div>  
                        
        </div>
    </form>   
</section>
<script>
    var cityIdArr = [];
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
        $('.city_ids').each(function(){ 
            cityIdArr.push($(this).val())
        });
    })
        

    function searchCities(name){
        var state_id = $('#state_id').val();
        if(name.length > 0) {
            $.ajax({
                url: "{{ route('admin.ledger.searchCities') }}",
                method: 'post',
                data: {
                    '_token': '{{ csrf_token() }}',
                    search: name,
                    idnotin: cityIdArr,
                    parent_id:state_id
                },
                success: function(result) {
                    // console.log(result);
                    var content = '';
                    if (result.length > 0) {
                        content += `<div class="dropdown-menu show city-dropdown" aria-labelledby="dropdownMenuButton" style="width: 491px;">`;

                        $.each(result, (key, value) => {
                            content += `<a class="dropdown-item" href="javascript: void(0)" onclick="fetchCity(${value.id},'${value.name}')">${value.name}</a>`;
                        })
                        content += `</div>`;
                        // $($this).parent().after(content);
                    } else {
                        content += `<div class="dropdown-menu show w-100 city-dropdown" aria-labelledby="dropdownMenuButton"><li class="dropdown-item">No city found</li></div>`;
                    }
                    $('#respDropCity').html(content);
                }
            });
        }   else {
            $('.city-dropdown').hide()
        } 
    }

    function fetchCity(id,name){        
        cityIdArr.push(id);        
        $('.stores_class').append(`<li id="cityli_`+id+`">`+name+` <a href="javascript:void(0);"  onclick="removeCity(`+id+`);"><i class="fa fa-close"></i></a><input type="hidden" class="city_ids" name="city_ids[]" value="`+id+`" ></li>`);
        $('.city-dropdown').hide();
        $('#searchCityText').val('');
        
    }

    function removeCity(id){
        // alert(id)
        console.log(cityIdArr);
        $('.stores_class > #cityli_'+id).remove();
        cityIdArr =  cityIdArr.filter(e => e!=id);
        console.log(cityIdArr);
    }

    function validateNum(evt) {
        var theEvent = evt || window.event;

        // Handle paste
        if (theEvent.type === 'paste') {
            key = event.clipboardData.getData('text/plain');
        } else {
        // Handle key press
            var key = theEvent.keyCode || theEvent.which;
            key = String.fromCharCode(key);
        }
        var regex = /[0-9]|\./;
        if( !regex.test(key) ) {
            theEvent.returnValue = false;
            if(theEvent.preventDefault) theEvent.preventDefault();
        }
    }

</script>
@endsection