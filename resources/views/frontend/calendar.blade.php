<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

{{--    <meta name="current_user_email" content="{{ Auth::user()->email }}">--}}

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAYNqIrc58KKRgaWvu7NLbx_k8BMpkDBcc&libraries=places" async></script> -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.min.css') }}" rel="stylesheet">

</head>
<body>
    <div id="app">

        <main class="frontend-main-content">

            <div id="top-nav" class="d-flex flex-column flex-md-row align-items-center">
{{--                <h5 class="my-0 mr-md-auto font-weight-normal">pezohi</h5>--}}

                <a class="my-0 mr-md-auto font-weight-bold navbar-brand" href="{{ url('/') }}">
                    <span class="badge brand-logo">P</span>{{ config('app.name', 'Laravel') }}
                </a>

                <a class="btn btn-outline-primary" href="{{ url('/login') }}">Sign in</a>
{{--                <a class="btn btn-primary" href="#">Sign up</a>--}}
            </div>



            <frontend-calendar-data-component :data="{{ $calendar }}"></frontend-calendar-data-component>
        </main>

    </div>
</body>
</html>
