<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', 'Агрегатор')</title>

    <link rel="stylesheet" href="{{ asset_skip_cache('lib/bootstrap4/css/bootstrap.min.css') }}">
    @stack('head')
    @include('layouts.css')
</head>
<body data-route="{{ str_replace('.', '/', request()->route()->getName()) }}">
    @include('layouts.menu')
{{--    @include('adminlte-templates::common.errors')--}}
{{--    @include('flash::message')--}}
    @include('layouts.flash')
    @yield('content')

    <script src="{{ asset_skip_cache('lib/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset_skip_cache('lib/bootstrap4/js/bootstrap.bundle.js') }}"></script>
    @stack('body-end')
    <script src="{{ asset_skip_cache('js/script.js') }}"></script>
</body>
</html>
