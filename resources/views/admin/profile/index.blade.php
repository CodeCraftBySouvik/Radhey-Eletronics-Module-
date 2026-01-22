@extends('admin.layouts.app')
@section('title') {{ $pageTitle }} @endsection
@section('content')
   <section>
    <div class="row">
        @if (Session::has('message'))
        <div class="alert alert-success" role="alert">
            {{ Session::get('message') }}
        </div>
        @endif
        <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.admin.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                        <h4 class="page__subtitle">Profile</h4>
                        <div class="form-group mb-3">
                            <label class="control-label" for="site_name">Name</label>
                            <input  class="form-control" type="text" placeholder="Enter name" id="name" name="name" value="{{ $profile->name }}" maxlength="200" />
                            @error('name') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                           <label class="control-label" for="site_title">Email</label>
                           <input class="form-control"  type="text"  placeholder="Enter Email ID"  id="email"  name="email" value="{{ $profile->email }}"  readonly  disabled />
                            @error('email') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="control-label" for="site_title">Phone</label>
                            <input class="form-control"  type="text"  placeholder="Enter Phone"  id="mobile"  name="mobile" value="{{ $profile->mobile }}"  readonly disabled />
                             @error('mobile') <p class="small text-danger">{{ $message }}</p> @enderror
                         </div>
                        
                       
                        <div class="form-group">
                            <button type="submit" class="btn btn-sm btn-success">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
          <div class="col-sm-6">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.admin.profile.changepassword') }}" enctype="multipart/form-data">
                    @csrf
                        <h4 class="page__subtitle">Change Password</h4>
                        <div class="form-group mb-3">
                            <label class="control-label" for="site_name">Current Password</label>
                            <input class="form-control"  type="password"  placeholder="Enter current password"  id="current_password"  name="current_password" value="" />
                            @error('current_password') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="control-label" for="site_title">New Password</label>
                            <input  class="form-control" type="password"  placeholder="Enter new password" id="password" name="password"  value=""  />
                            @error('password') <p class="small text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label class="control-label" for="site_title">Confirm Password</label>
                            <input class="form-control" type="password"  placeholder="Enter confirm password"  id="password_confirmation" name="password_confirmation"  value=""  />
                            @error('password_confirmation') <p class="small text-danger">{{ $message }}</p> @enderror
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
<script>
    $(document).ready(function(){
        $('div.alert').delay(3000).slideUp(300);
    })
</script>
@endsection