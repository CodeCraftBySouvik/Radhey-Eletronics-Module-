@extends('admin.layouts.app')
@section('page', 'Sub Category Details')
@section('content')
<section>
    <div class="row">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">                        
                        <div class="col-md-12">                            
                            <p class="">Category : <strong>{{ $data->category->name }}</strong></p>
                            <p class="">Name : <strong>{{ $data->name }}</strong></p>
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
                    <form method="POST" action="{{ route('admin.subcategory.update', $data->id) }}" enctype="multipart/form-data">
                    @csrf
                        <h4 class="page__subtitle">Edit Sub Category</h4>
                        <div class="form-group mb-3">
                            <label class="label-control">Category <span class="text-danger">*</span> </label>
                            <select class="form-control" name="cat_id">
                                <option hidden selected>Select category...</option>
                                @foreach ($categories as $index => $item)
                                    <option value="{{$item->id}}" {{ ($item->id == $data->cat_id) ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @error('cat_id') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-control">Name <span class="text-danger">*</span> </label>
                            <input type="text" name="name" placeholder="Enter description" class="form-control" value="{{ $data->name }}" maxlength="200">
                            @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-control">Description </label>
                            <textarea name="description" class="form-control" rows="10">{{$data->description}}</textarea>
                            @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>                        
                        <div class="form-group">
                            <a href="{{ route('admin.subcategory.index') }}" class="btn btn-sm btn-danger">Back</a>
                            <button type="submit" class="btn btn-sm btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
