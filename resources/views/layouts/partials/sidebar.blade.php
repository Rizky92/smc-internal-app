<aside class="main-sidebar sidebar-light-olive elevation-1">
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="{{ asset('img/logo.png') }}" alt="AdminLTE Logo" class="brand-image" style="opacity: .8">
        <span class="brand-text">{{ config('app.name') }}</span>
    </a>

    <div class="sidebar">
        @auth('web')
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <img src="{{ asset('img/avatar.png') }}" class="img-circle" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block" style="line-height: 1.1">
                        <span>{{ auth()->user()->nama }}</span> <br>
                        <span class="text-xs">{{ auth()->user()->user_id }}</span>
                    </a>
                </div>
            </div>
        @endauth

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                @foreach ($sidebarMenu as $item)
                    @switch($item['type'])
                        @case('dropdown')
                            @include('layouts.components.sidebar.dropdown', $item)
                            @break
                        @case('title')
                            
                            @break
                        @default
                            @include('layouts.components.sidebar.link', $item)
                    @endswitch
                @endforeach
            </ul>
        </nav>
    </div>
</aside>