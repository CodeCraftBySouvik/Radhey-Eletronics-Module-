@extends('admin.layouts.app')
@section('page', 'Designation Details')
@section('content')
<section>
    <form method="POST" action="{{ route('admin.designation.update', $data->id) }}">
        @csrf
    <div class="row">        
        <div class="col-sm-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5>{{ $data->name }}</h5>
                            <hr>
                            <div class="card shadow-sm">
                                <div class="row">
                                    
                                    @foreach ($roles as $role)
            
                                    @php
                                        $checked = "";
                                        if(in_array($role->id,$roleIdArr)){
                                            $checked = "checked";
                                        }
                                    @endphp
                                    <div class="col-sm-4">
                                        <div class="form-group mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" {{$checked}} value="{{$role->id}}" id="{{$role->value}}" name="role_ids[]">
                                                <label class="form-check-label" for="{{$role->value}}">
                                                    {{$role->name}}
                                                </label>
                                            </div>
                                        </div>
                                    </div>                                  
                                    @endforeach                                       
                                </div>                    
                            </div>  
                        </div>                        
                    </div>                    
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">                
                    <h4 class="page__subtitle">Edit Designation</h4>
                    <div class="form-group mb-3">
                        <label class="label-control">Name <span class="text-danger">*</span> </label>
                        <input type="text" name="name" placeholder="Enter name" class="form-control" value="{{ $data->name }}">
                        @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                    </div>                                             
                    <div class="form-group">
                        <a href="{{ route('admin.designation.index') }}" class="btn btn-sm btn-danger">Back</a>
                        <button type="submit" class="btn btn-sm btn-success">Update</button>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
    </form>
</section>
@endsection
