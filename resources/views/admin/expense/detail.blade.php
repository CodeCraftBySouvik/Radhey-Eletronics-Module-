@extends('admin.layouts.app')
@section('page', 'Expense Details')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-8 order-2 order-xl-1">
            <div class="card">
                <div class="card-body">                    
                        <div class="admin__content">
                            <aside>
                                <nav>Expense Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Type</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ $data->parent_title }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Title</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ $data->title }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Description</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ $data->description }}</p>
                                    </div>
                                </div>
                                @if (!empty($data->for_credit) )
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Credit Purpose</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ !empty($data->for_credit) ? 'Yes' : 'No' }}</p>
                                    </div>
                                </div>    
                                @endif                                
                                @if (!empty($data->for_debit))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">Debit Purpose</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ !empty($data->for_debit) ? 'Yes' : 'No' }}</p>
                                    </div>
                                </div>    
                                @endif
                                @if (!empty(!empty($data->for_staff)))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">For Staff</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ !empty($data->for_staff) ? 'Yes' : 'No' }}</p>
                                    </div>
                                </div>    
                                @endif
                                @if (!empty($data->for_store))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">For Store</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ !empty($data->for_store) ? 'Yes' : 'No' }}</p>
                                    </div>
                                </div>   
                                @endif
                                @if (!empty($data->for_partner))
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="" class="col-form-label">For Partner</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ !empty($data->for_partner) ? 'Yes' : 'No' }}</p>
                                    </div>
                                </div>   
                                @endif
                                
                            </content>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4 order-1 order-xl-2">
            <div class="card">                
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.expense.update', $data->id) }}">
                    @csrf
                        <h4 class="page__subtitle">Edit</h4>
                        <input type="hidden" name="parent_id" value="{{$data->parent_id}}">                        
                        <div class="form-group mb-3">
                            <label class="label-control">Title <span class="text-danger">*</span> </label>
                            <input type="text" name="title" placeholder="" class="form-control" value="{{ $data->title }}">
                            @error('title') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-control">Description </label>
                            <textarea name="description" class="form-control" rows="5">{{$data->description}}</textarea>
                            @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>  
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="1" name="for_debit" @if($data->for_debit == 1) checked @endif id="for_debit">
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
                                        <input class="form-check-input" type="checkbox" value="1" name="for_credit" @if($data->for_credit == 1) checked @endif  id="for_credit">
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
                                        <input class="form-check-input" type="checkbox" value="1" name="for_staff" @if($data->for_staff == 1) checked @endif  id="for_staff">
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
                                        <input class="form-check-input" type="checkbox" value="1" name="for_store" @if($data->for_store == 1) checked @endif  id="for_store">
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
                                        <input class="form-check-input" type="checkbox" value="1" name="for_partner" @if($data->for_partner == 1) checked @endif  id="for_partner">
                                        <label class="form-check-label" for="for_partner">
                                          For Partner
                                        </label>
                                    </div>
                                    @error('for_partner') <p class="small text-danger">{{ $message }}</p> @enderror
                                </div> 
                            </div>
                        </div>
                                             
                        <div class="form-group">
                            <a href="{{ route('admin.expense.index', $data->parent_id) }}" class="btn btn-sm btn-danger">Back</a>
                            <button type="submit" class="btn btn-sm btn-success">Update</button>
                            
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
