@extends('admin.layouts.app')

@section('page', 'Unit detail')

@section('content')
<section>
    <div class="row">
        <div class="col-sm-8">
            <div class="card">
                <div class="card-body">
                    
                        <div class="admin__content">
                            <aside>
                                <nav>Unit Information</nav>
                            </aside>
                            <content>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Unit Name</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ $data->name }}</p>
                                    </div>
                                </div>
                                <div class="row mb-2 align-items-center">
                                    <div class="col-3">
                                        <label for="inputPassword6" class="col-form-label">Unit Description</label>
                                    </div>
                                    <div class="col-9">
                                        <p class="m-0">{{ $data->description }}</p>
                                    </div>
                                </div>
                            </content>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.unit.update', $data->id) }}">
                    @csrf
                        <h4 class="page__subtitle">Edit</h4>
                        @if($errors->any())                            
                                                       
                            {!! implode('', $errors->all('<p class="small text-danger">:message</p>')) !!}
                        @endif
                        <div class="form-group mb-3">
                            <label class="label-control">Name <span class="text-danger">*</span> </label>
                            <input type="text" name="name" placeholder="" class="form-control" value="{{ $data->name }}">
                            @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="label-control">Description </label>
                            <textarea name="description" class="form-control">{{$data->description}}</textarea>
                            @error('description') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-sm btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
