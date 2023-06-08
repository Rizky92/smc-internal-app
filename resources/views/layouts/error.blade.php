<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> {{ $code }} {{ $title }} - {{ config('app.name') }}</title>

    <link href="{{ asset('css/fontawesome5-all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/adminlte.min.css') }}" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}">
    @stack('css')
</head>

<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand-md navbar-light navbar-white">
            <div class="container">
                <a href="{{ route('admin.dashboard') }}" class="navbar-brand">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo {{ config('app.name') }}" class="brand-image" style="opacity: .8">
                    <span class="brand-text text-sm">{{ config('app.name') }}</span>
                </a>
            </div>
        </nav>

        <div class="content-wrapper pt-4">
            <section class="content">
                <div class="error-page">
                    <h2 class="headline text-{{ $level }}">{{ $code }}</h2>

                    <div class="error-content">
                        <h3>
                            <svg xmlns="http://www.w3.org/2000/svg" class="text-{{ $level }}" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                <path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.7 2.983h16.845a1.989 1.989 0 0 0 1.7 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.4 0z"></path>
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                            </svg>
                            {{ $title }}
                        </h3>

                        <p>{{ $message }}</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>

</html>
