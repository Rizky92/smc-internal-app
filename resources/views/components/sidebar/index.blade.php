@aware(['nama', 'nip'])

<aside class="main-sidebar sidebar-light-olive border-right">
    <a href="{{ route('admin.dashboard') }}" class="brand-link text-sm">
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
                        <span>{{ $nama }}</span> <br>
                        <span class="text-xs">{{ $nip }}</span>
                    </a>
                </div>
            </div>
        @endauth

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                {{ $slot }}
            </ul>
        </nav>
    </div>
</aside>