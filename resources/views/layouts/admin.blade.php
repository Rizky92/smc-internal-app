<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ config('app.name') }}</title>

    <link href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/adminlte.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}">
    <style>
        .custom-control-label {
            user-select: none !important;
        }

        .btn {
            display: inline-flex !important;
            align-items: center !important;
        }

        .btn::after {
            margin-top: 0.125rem
        }

        .select2-selection__arrow {
            top: 0 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 2rem !important;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            padding-left: 0 !important;
            margin-left: -0.125rem !important;
        }

        .sidebar-search-results .list-group-item {
            color: #111827 !important;
            background-color: #fff !important;
        }

        .sidebar-search-results .list-group-item .text-light {
            color: #111827 !important;
            font-weight: 700 !important;
        }

        .sidebar-search-results .list-group-item:hover, .sidebar-search-results .list-group-item:focus {
            background-color: #e5e7eb !important
        }

        .table {
            margin-bottom: 0 !important
        }
    </style>
    @livewireStyles

    @stack('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed bg-light">
    <div class="wrapper">
        <x-navbar />
        <x-sidebar>
            @foreach ($sidebarMenu as $menu)
                @switch($menu['type'])
                    @case('dropdown')
                        <x-sidebar.dropdown
                            :hasPermissions="$menu['hasAnyPermissions']"
                            :isActive="in_array($current, Arr::flatten($menu['items']), true)"
                            :icon="$menu['icon']"
                            :name="$menu['name']"
                        >
                            @foreach ($menu['items'] as $submenu)
                                <x-sidebar.link 
                                    :hasPermissions="$submenu['hasAnyPermissions']"
                                    :current="$current"
                                    :url="$submenu['url']"
                                    :icon="$submenu['icon']"
                                    :name="$submenu['name']"
                                />
                            @endforeach
                        </x-sidebar.dropdown>
                    @break

                    @case('link')
                        <x-sidebar.link
                            :hasPermissions="$menu['hasAnyPermissions']"
                            :current="$current"
                            :url="$menu['url']"
                            :icon="$menu['icon']"
                            :name="$menu['name']"
                        />
                    @break
                @endswitch
            @endforeach

            <x-slot name="footer" class="justify-content-center align-items-center">
                <span class="text-sm font-weight-bold text-muted hide-on-collapse">{{ request()->ip() }}</span>
            </x-slot>
        </x-sidebar>

        <div class="content-wrapper">
            <x-page-title :title="$title" />
            <section class="content">
                <div class="container-fluid">
                    {{ $slot }}
                </div>
            </section>
        </div>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('js/adminlte.min.js') }}"></script>
    @stack('js')

    @livewireScripts
</body>

</html>
