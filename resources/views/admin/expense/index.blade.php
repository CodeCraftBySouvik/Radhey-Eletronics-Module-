@extends('admin.layouts.app')
@if ($parent_id == 1)
    @section('page', 'Recurring Expense')
@else
    @section('page', 'Non Recurring Expense')
@endif
@section('content')
<section>
    <div class="row">
        <div class="col-sm-8 order-2 order-xl-1">
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
                        <form action="{{ route('admin.expense.index', $parent_id) }}" id="searchForm">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <select name="cred_deb" class="form-control" id="cred_deb">
                                    <option value="">All Purpose</option>
                                    <option value="credit" @if($cred_deb == 'credit') selected @endif>Credit Purpose</option>
                                    <option value="debit" @if($cred_deb == 'debit') selected @endif>Debit Purpose</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="user_type" class="form-control" id="user_type">
                                    <option value="">User Type - All</option>
                                    <option value="store" @if($user_type == 'store') selected @endif>For Store</option>
                                    <option value="staff" @if($user_type == 'staff') selected @endif>For Staff</option>
                                    <option value="partner" @if($user_type == 'partner') selected @endif>For Partner</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <input type="search" id="term" name="term" value="{{$term}}" class="form-control" placeholder="Search here..">
                            </div>
                            <input type="submit" hidden />
                        </div>
                        </form>
                    </div>                           
                </div>
            </div>
            <form action="{{ route('admin.expense.bulkSuspend') }}" method="post">
                @csrf
                <input type="hidden" name="parent_id" id="parent_id" value="{{$parent_id}}">
                <div class="filter">
                    <div class="row align-items-center justify-content-between">
                        <div class="col">
                            <div class="row g-3 align-items-center">
                                
                                <div class="col-auto">
                                    <button id="btnSuspend" type="submit" class="btn btn-outline-danger btn-sm">Suspend</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-auto">                            
                            <p>{{$total}} Items</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="check-column">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="checkAll">
                                        <label class="form-check-label" for=""></label>
                                    </div>
                                </th>
                                <th>Published Date</th>
                                <th>Title</th>
                                <th>Debit Purpose</th>
                                <th>Credit Purpose</th>
                                <th>For Store</th>
                                <th>For Staff</th>
                                <th>For Partner</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data as $index => $item)

                            @php
                            if (!empty($_GET['status'])) {
                                if ($_GET['status'] == 'active') {
                                    if ($item->status == 0) continue;
                                } else {
                                    if ($item->status == 1) continue;
                                }
                            }
                            @endphp


                            {{-- @forelse ($data as $index => $item) --}}
                            <tr>
                                <td class="check-column">
                                    @if ($item->status != 0)
                                    <div class="form-check">
                                        <input name="suspend_check[]" class="data-check" type="checkbox"  value="{{$item->id}}">
                                    </div>
                                    @endif
                                </td>  
                                <td>{{date('d/m/Y', strtotime($item->created_at))}}</td>                                  
                                <td>
                                {{$item->title}}
                                <div class="row__action">
                                    <a href="{{ route('admin.expense.view', $item->id) }}">Edit</a>
                                    <a href="{{ route('admin.expense.view', $item->id) }}">View</a>
                                    <a href="{{ route('admin.expense.status', $item->id) }}">{{($item->status == 1) ? 'Suspend' : 'Active'}}</a>                                      
                                </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{($item->for_debit == 1) ? 'success' : 'danger'}}">{{($item->for_debit == 1) ? 'Yes' : 'No'}}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{($item->for_credit == 1) ? 'success' : 'danger'}}">{{($item->for_credit == 1) ? 'Yes' : 'No'}}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{($item->for_store == 1) ? 'success' : 'danger'}}">{{($item->for_store == 1) ? 'Yes' : 'No'}}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{($item->for_staff == 1) ? 'success' : 'danger'}}">{{($item->for_staff == 1) ? 'Yes' : 'No'}}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{($item->for_partner == 1) ? 'success' : 'danger'}}">{{($item->for_partner == 1) ? 'Yes' : 'No'}}</span>
                                </td>
                                <td><span class="badge bg-{{($item->status == 1) ? 'success' : 'danger'}}">{{($item->status == 1) ? 'Active' : 'Suspend'}}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="100%" class="small text-muted">No data found</td></tr>
                            @endforelse
                        </tbody>
                    </table> 
                </div>
            </form> 
            {{ $data->links() }} 
        </div>
        <div class="col-sm-4 order-1 order-xl-2">
            <div class="card">               
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.expense.store') }}" >
                    @csrf
                        <h4 class="page__subtitle">Add New</h4>
                        <input type="hidden" name="parent_id" value="{{$parent_id}}">                         
                        <div class="form-group mb-3">
                            <label class="label-control">Title <span class="text-danger">*</span> </label>
                            <input type="text" name="title" placeholder="" class="form-control" value="{{old('title')}}">
                            @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-control">Description </label>
                            <textarea name="description" class="form-control" rows="5">{{old('description')}}</textarea>
                            @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div> 
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="for_debit" id="for_debit">
                                        <label class="form-check-label" for="for_debit">
                                          Debit Purpose
                                        </label>
                                    </div>
                                    @error('for_debit') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>  
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="for_credit" id="for_credit">
                                        <label class="form-check-label" for="for_credit">
                                          Credit Purpose
                                        </label>
                                    </div>
                                    @error('for_credit') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div> 
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="for_staff" id="for_staff">
                                        <label class="form-check-label" for="for_staff">
                                          For Staff
                                        </label>
                                    </div>
                                    @error('for_staff') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>  
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="for_store" id="for_store">
                                        <label class="form-check-label" for="for_store">
                                          For Store
                                        </label>
                                    </div>
                                    @error('for_store') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>  
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="for_partner" id="for_partner">
                                        <label class="form-check-label" for="for_partner">
                                          For Partner
                                        </label>
                                    </div>
                                    @error('for_partner') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div>  
                            </div>
                        </div>                       
                                                
                        <div class="form-group">
                            <button type="submit" class="btn btn-sm btn-success">Add</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
        $('#btnSuspend').prop('disabled', true);        
        $("#checkAll").change(function () {
            $(".data-check").prop('checked', $(this).prop("checked"));
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
        $('#searchForm').submit();
    });

    $('#cred_deb').on('change', function(){
        $('#searchForm').submit();
    });
    $('#user_type').on('change', function(){
        $('#searchForm').submit();
    })

    
</script>
@endsection

