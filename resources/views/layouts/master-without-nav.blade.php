<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>
        <meta charset="utf-8" />
        <title> @yield('title') | {{ config('app.name') }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Gingerminds" name="author" />
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
        @include('gingerminds-core::layouts.partials.hreflang')
        @stack('head')

        @include('gingerminds-core::layouts.head-css')
  </head>

    @yield('body')

    @yield('content')

    @include('gingerminds-core::layouts.vendor-scripts')
    </body>
</html>
