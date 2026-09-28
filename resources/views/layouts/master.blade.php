<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8"/>
    <title> @yield('title') | {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="Gingerminds" name="author"/>
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ URL::asset('build/images/favicon.ico') }}">
    @include('gingerminds-core::layouts.partials.hreflang')
    @stack('head')
    @include('gingerminds-core::layouts.head-css')
</head>

@section('body')
    <body>
    @show
    <!-- Begin page -->
    <div class="container-fluid vh-100 d-flex flex-column p-0">
        <div class="d-flex flex-grow-1">
            @include('gingerminds-core::layouts.sidebar.sidebar')

            <main class="flex-grow-1 d-flex flex-column" style="min-width: 0;">

                <div class="flex-grow-1 p-4 overflow-auto">
                    @yield('breadcrumb')
                    @include('gingerminds-core::components.alert.alert')
                    @yield('content')
                </div>

                @include('gingerminds-core::layouts.footer.footer')
            </main>
        </div>
    </div>

    @stack('modals')

    <!-- JAVASCRIPT -->
    @include('gingerminds-core::layouts.vendor-scripts')
    </body>

</html>
