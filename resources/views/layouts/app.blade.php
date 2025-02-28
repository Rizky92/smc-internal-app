<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta http-equiv="X-UA-Compatible" content="ie=edge" />
        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="stylesheet" href="{{ asset('css/dataTables.bootstrap4.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('css/responsive.bootstrap4.min.css') }}" />
        <link href="{{ asset('css/adminlte.min.css') }}" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}" />

        @stack('styles')

        @livewireStyles
    </head>

    <body>
        @yield('fitur-baru')

        @yield('informasi-kamar')

        @yield('display-jadwal-dokter')

        <div>
            @yield('antrian-poli')
        </div>

        {{ $slot }}

        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('js/jquery.overlayScrollbars.min.js') }}"></script>
        <script src="{{ asset('js/adminlte.min.js') }}"></script>

        @stack('js')

        @livewireScripts
    </body>
</html>
