<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>{{ $title }} - {{ config('app.name') }}</title>

        <link href="{{ asset('css/fontawesome5-all.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/adminlte.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('css/OverlayScrollbars.min.css') }}" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="{{ asset('logo.ico') }}" />
        <style>
            .custom-control-label {
                user-select: none !important;
            }

            .sidebar-search-results .list-group-item {
                color: #111827 !important;
                background-color: #fff !important;
            }

            .sidebar-search-results .list-group-item .text-light {
                color: #111827 !important;
                font-weight: 700 !important;
            }

            .sidebar-search-results .list-group-item:hover,
            .sidebar-search-results .list-group-item:focus {
                background-color: #e5e7eb !important;
            }

            .nav-flat.nav-sidebar > .nav-item .nav-treeview .nav-item > .nav-link,
            .nav-flat.nav-sidebar > .nav-item > .nav-treeview .nav-item > .nav-link {
                border-left: 0 !important;
            }
        </style>
        @stack('css')

        @livewireStyles
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
                                :name="$menu['name']">
                                @foreach ($menu['items'] as $submenu)
                                    <x-sidebar.link :hasPermissions="$submenu['hasAnyPermissions']" :current="$current" :url="$submenu['url']" :icon="$submenu['icon']" :name="$submenu['name']" />
                                @endforeach
                            </x-sidebar.dropdown>

                            @break
                        @case('link')
                            <x-sidebar.link :hasPermissions="$menu['hasAnyPermissions']" :current="$current" :url="$menu['url']" :icon="$menu['icon']" :name="$menu['name']" />

                            @break
                    @endswitch
                @endforeach

                <x-slot name="footer" class="justify-content-center align-items-center">
                    <span class="text-sm font-weight-bold text-muted hide-on-collapse">
                        {{ request()->ip() }}
                    </span>
                </x-slot>
            </x-sidebar>

            <div class="content-wrapper">
                <x-page-title :title="$title" />
                <div class="px-3">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('js/jquery.overlayScrollbars.min.js') }}"></script>
        <script src="{{ asset('js/adminlte.min.js') }}"></script>
        @stack('js')

        @livewireScripts
    </body>
</html>
