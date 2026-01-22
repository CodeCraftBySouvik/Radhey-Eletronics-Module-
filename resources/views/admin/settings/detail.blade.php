@extends('admin.layouts.app')

@section('page', 'Settings detail')

@section('content')
<section>
    <div class="row">
        <div class="col-sm-8">
            <div class="card">    
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-muted small mb-1">Page</p>
                            <p class="text-dark small">{{strtoupper($data->page_heading)}}</p>

                            <p class="text-muted small mb-1">Content</p>
                            <p class="text-dark small">{{$data->content}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.update', $data->id) }}">
                    @csrf
                        <h4 class="page__subtitle">Edit</h4>
                        <div class="form-group mb-3">
                            <label class="label-control">Content </label>
                            <textarea name="content" class="form-control">{{$data->content}}</textarea>
                            @error('content') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-sm btn-danger">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection