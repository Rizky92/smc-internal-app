<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} - {{ config('app.name') }}</title>

    <link href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/adminlte.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}" rel="stylesheet">
    @livewireStyles

    @stack('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed bg-light">
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
