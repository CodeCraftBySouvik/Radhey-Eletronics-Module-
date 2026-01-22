@extends('admin.layouts.app')
@section('page', 'Category Details')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h3>{{ $data->name }}</h3>
                            <p class="small">{{ $data->description }}</p>
                            <hr>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.category.update', $data->id) }}" enctype="multipart/form-data">
                    @csrf
                        <h4 class="page__subtitle">Edit Category</h4>
                        <div class="form-group mb-3">
                            <label class="label-control">Name <span class="text-danger">*</span> </label>
                            <input type="text" name="name" placeholder="Enter name" class="form-control" value="{{ $data->name }}">
                            @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        
                        <div class="form-group mb-3">
                            <label class="label-control">Description </label>
                            <textarea name="description" class="form-control" rows="10" placeholder="Enter description">{{$data->description}}</textarea>
                            @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>                        
                        <div class="form-group">
                            <a href="{{ route('admin.category.index') }}" class="btn btn-sm btn-danger">Back</a>
                            <button type="submit" class="btn btn-sm btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
