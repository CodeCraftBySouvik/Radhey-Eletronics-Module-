<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/style.css') }}" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css" />

    <title>{{ config('app.name') }} | admin panel</title>
  </head>
  <body>
    <main class="login">
      <!--<div class="login__left">
        <img src="{{ asset('admin/images/onn_outerwear.png') }}">
      </div>-->
      <div class="login__right">
        <div class="login__block">
          <div class="logo__block">
            <img src="{{ asset('admin/images/TRINETRlogo.png') }}">
          </div>

          @if (Session::get('success'))<div class="alert alert-success">{{ Session::get('success') }}</div>@endif
          @if (Session::get('failure'))<div class="alert alert-danger">{{ Session::get('failure') }}</div>@endif

          @if (Session::has('logout-message'))
          <div class="alert alert-info" role="alert">
              {{ Session::get('logout-message') }}
          </div>
          @endif

          <form method="POST" action="{{ route('admin.login.check') }}">
          @csrf
            {{-- <div class="form-floating mb-3">
              <input type="email" class="form-control" name="email" value="{{ old('email') }}" id="floatingInput" placeholder="name@example.com">
              <label for="floatingInput">Email address</label>
            </div> --}}
            {{-- @error('email') <p class="small text-danger">{{ $message }}</p> @enderror --}}
            <div class="form-floating mb-3">
              <input type="text" class="form-control" name="mobile" value="{{ old('mobile') }}" id="floatingInput" maxlength="10" onkeypress="validateNum(event)">
              <label for="floatingInput">Mobile No</label>
            </div>

            <div class="form-floating mb-3">
              <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Password" maxlength="20">
              <label for="floatingPassword">Password</label>
            </div>
            {{-- @error('password') <p class="small text-danger">{{ $message }}</p> @enderror --}}

            <div class="row mb-3">
              <div class="col-6">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                  <label class="form-check-label" for="flexCheckDefault">
                    Remember Me
                  </label>
                </div>
              </div>
              {{-- <div class="col-6 text-end">
                <a href="#">Forgot Password?</a>
              </div> --}}
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-lg btn-primary">Login</button>
            </div>
          </form>

          {{-- <div class="row mt-3">
              <div class="col-12 text-center">
                <a href="{{ url('/') }}">Back to homepage</a>
              </div>
          </div> --}}
        </div>
      </div>
    </main>
    <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    <script>
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

      $(document).ready(function(){
        $('div.alert').delay(10000).slideUp(300);

        // Reload page for avoiding csrf token expiration
        const reloadVal = 119000; // 119 seconds 
        let idleTimer = null;
        idleTimer = setTimeout(function () {
            location.reload(true);
            console.log('Reloading ...');
        }, reloadVal);
        
      });

    </script>

  </body>
</html>
