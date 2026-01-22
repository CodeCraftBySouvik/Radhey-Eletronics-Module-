<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        @if( !request()->is('login') && !request()->is('login/otp*') && !request()->is('register') )
        <nav class="topnavbar navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <!-- {{ config('app.name', 'Laravel') }} -->
                    <img src="{{asset('img/logo-square.png')}}" alt="">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto"></ul>

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a href="{{ route('front.catalouge.index') }}" class="nav-link" href="">Catalouge</a></li>
                        <li class="nav-item"><a href="{{ route('front.store.order.call.index') }}" class="nav-link" href="">Order on call</a></li>
                        <li class="nav-item"><a href="{{ route('front.store.index') }}" class="nav-link" href="">Store visit</a></li>
                        @auth
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('front.cart.index') }}">Cart @if($cartCount > 0)<span class="badge badge-danger">{{ $cartCount }}</span>@endif </a>
                            </li>
                        @endauth
                        @guest
                            <li class="nav-item link__login">
                                {{-- <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a> --}}
                            </li>
                            {{-- @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif --}}
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>
        @endif

        <main class="mainbody">
            @yield('content')
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('script')

    <script>
        // sweetalert fires | type = success, error, warning, info, question
        function toastFire(type = 'success', title, body = '') {
            Swal.fire({
                icon: type,
                title: title,
                text: body,
                confirmButtonColor: '#c10909',
                timer: 5000
            })
        }

        // on session toast fires
        @if (Session::get('success'))
            toastFire('success', '{{ Session::get('success') }}');
        @elseif (Session::get('failure'))
            toastFire('danger', '{{ Session::get('failure') }}');
        @endif
    </script>
    <script>
        $('.storeCatgoryList a' ).on( 'click', function(e){
            var href = $(this).attr( 'href' );
            $( 'html, body' ).animate({
                scrollTop: $( href ).offset().top - 80
            } );
            e.preventDefault();
            $(this).parent().addClass("current");
            $(this).parent().siblings().removeClass("current");

        });
    </script>
</body>
</html>
