@extends('admin.layouts.app')
@if($is_editable)
    @section('page', 'Edit Weekly Task')
@else
@section('page', 'View Weekly Task')
@endif
@section('content')
<section>
    <ul class="breadcrumb_menu">
        <li>Master</li>
        <li>Staff Management</li>
        <li><a href="{{ route('admin.staff.index') }}">Staff</a></li> 
        <li><a href="{{ route('admin.staff.listtask', $user_id) }}">List Task</a></li>       
        <li>View Weekly Task</li>
    </ul>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.staff.updatetask') }}" method="POST">
                        @csrf
                        <input type="hidden" name="id" value="{{$id}}">
                        <input type="hidden" name="user_id" value="{{$user_id}}">
                        @if (\Session::has('message'))
                        <div class="alert alert-success">
                            <ul>
                                <li>{!! \Session::get('message') !!}</li>
                            </ul>
                        </div>
                        @endif
                        @if($errors->any())                            
                                                       
                            {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
                        @endif
                        <h4 class="page__subtitle">Assign Task</h4>                 
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="">From</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" placeholder="Start Date" value="{{$data[0]->start_date}}" readonly {{$disabled}}>
                                </div>
                            </div> 
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <label for="">To</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" placeholder="End Date" value="{{$data[0]->end_date}}" readonly {{$disabled}}>
                                </div>
                            </div>                            
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-sm" id="timePriceTable">
                                    <thead>
                                        <tr>
                                            <th>Store</th>
                                            <th>No of Visit</th>
                                            <th>Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $count=1;
                                        @endphp
                                        @forelse ($data as $items)
                                        <tr id="tr_{{$count}}" class="tr_store">
                                            <td>  
                                                <select required {{$disabled}} name="store[]" id="name" class="form-control store-dropdown" placeholder="Store name" onchange="checkStoreExists(this.value,{{$count}});">
                                                    <option value="" hidden>-select-</option>
                                                    @foreach ($stores as $s)
                                                        <option value="{{$s->id}}" @if($s->id == $items->store_id) selected @endif>{{$s->store_name}}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" value="{{$items->store_id}}" name="store_id[]" id="store_id{{$count}}">
                                            </td>
                                            <td>
                                                <select name="no_of_visit[]" {{$disabled}} required class="form-control" id="no_of_visit{{$count}}">
                                                    @for ($i=1;$i<10;$i++)
                                                    <option value="{{$i}}" @if($i == $items->no_of_visit) selected @endif>{{$i}}</option>
                                                    @endfor
                                                </select>
                                            </td>
                                            <td>
                                                <textarea name="comment[]" {{$disabled}} placeholder="Enter comments" class="form-control" id="comment{{$count}}" cols="30" rows="10">{{$items->comment}}</textarea>
                                            </td>   
                                            @if($is_editable)                                        
                                            <td id="btn_td_1">
                                                <a class="btn btn-sm btn-success actionTimebtn addNewTime">+</a>
                                                <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow({{$count}})">X</a>
                                            </td>
                                            @endif
                                        </tr>
                                        @php
                                            $count++;
                                        @endphp
                                        @empty
                                            
                                        @endforelse
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <a href="{{ route('admin.staff.listtask', $user_id) }}" class="btn btn-sm btn-danger">Back</a>
                                    @if($is_editable)
                                    <button type="submit"  class="btn btn-sm btn-success">Submit</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>        
    </div>
</section>
<script type="text/javascript">
    var i = {{count($data) + 1}};
    $(document).on('click','.addNewTime',function(){
        var thisClickedBtn = $(this);
        
        var toAppend = `
            <tr id="tr_`+i+`" class="tr_store">
                <td>  
                    <select required name="store[]" id="name" class="form-control store-dropdown" placeholder="Store name" onchange="checkStoreExists(this.value,`+i+`);">
                        <option value="" hidden>-select-</option>
                        @foreach ($stores as $item)
                            <option value="{{$item->id}}">{{$item->store_name}}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="store_id[]" id="store_id`+i+`">
                </td>
                <td>
                    <select name="no_of_visit[]" required class="form-control" id="no_of_visit`+i+`">
                        @for ($i=1;$i<10;$i++)
                        <option value="{{$i}}">{{$i}}</option>
                        @endfor
                    </select>
                </td>
                <td>
                    <textarea name="comment[]" placeholder="Enter comments" class="form-control" id="comment`+i+`" cols="30" rows="10"></textarea>
                </td>                                            
                <td id="btn_td_1">
                    <a class="btn btn-sm btn-success actionTimebtn addNewTime">+</a>
                    <a class="btn btn-sm btn-danger actionTimebtn removeTimePrice" onclick="removeRow(`+i+`)">X</a>
                </td>
            </tr>
        `;

        $('#timePriceTable').append(toAppend);
        i++;
    });

    function removeRow(i){        
        var count_tr_store = $('.tr_store').length;
        if(count_tr_store > 1){
            $('#tr_'+i).remove();
        }        
    }

    function checkStoreExists(e,number)
    {
        // alert(e);
        $("#store_id" + number).val(e);
        var inputs = $(".store-dropdown");
        var storIdArr = [];

        for(var i = 0; i < inputs.length; i++){
            // alert($(inputs[i]).val());
            storIdArr.push($(inputs[i]).val());
            
        }
        console.log(storIdArr);
        
    }

</script>      
@endsection
