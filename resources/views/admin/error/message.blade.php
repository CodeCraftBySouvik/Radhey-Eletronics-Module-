<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="{{ asset('admin/css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('admin/css/style.css') }}" rel="stylesheet">

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
                    <img src="{{ asset('admin/images/workmaxlogo 1.png') }}">
                </div>
                
                <div class="row mb-3">
                    <p>{{$message}}</p>
                
                </div>
                <div class="d-grid">
                    <a href="{{route('admin.home')}}" class="btn btn-lg btn-primary">Back To Dashboard</a>
                </div>            
            </div>
        </div>
        </main>
        <script src="{{ asset('admin/js/bootstrap.bundle.min.js') }}"></script>
    
    </body>
</html>
