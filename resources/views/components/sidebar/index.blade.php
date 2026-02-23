@aware(['nama', 'nik'])

@props(['footer' => null])

<aside class="main-sidebar main-sidebar-custom sidebar-light-olive border-right">
    <a href="{{ route('admin.dashboard') }}" class="brand-link text-sm border-right">
        <img src="{{ asset('img/logo.png') }}" alt="Logo {{ config('app.name') }}" class="brand-image" style="opacity: 0.8" />
        <span class="brand-text">{{ config('app.name') }}</span>
    </a>

    <div class="sidebar" style="height: calc(100% - ((3.5rem + 2rem) + 1px))">
        @auth('web')
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <img src="{{ asset('img/avatar.png') }}" class="img-circle" alt="User Image" />
                </div>
                <div class="info">
                    <a href="#" class="d-flex flex-column" style="line-height: 1.2rem">
                        @impersonating
                        <span class="text-xs">Melihat sebagai :</span>
                        @endImpersonating
                        <span class="text-sm">{{ $nama }}</span>
                        <span class="text-xs">{{ $nik }}</span>
                    </a>
                </div>
            </div>
        @endauth

        {{-- <livewire:sidebar-menu /> --}}

        <div class="form-inline">
            <div class="input-group input-group-sm" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar bg-white" type="search" placeholder="Cari menu..." aria-label="Cari menu" autocomplete="off" />
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <nav class="mt-3">
            <ul class="nav nav-pills nav-sidebar flex-column nav-flat" data-widget="treeview" role="menu" data-accordion="false">
                {{ $slot }}
            </ul>
        </nav>
    </div>

    @if ($footer)
        <div {{ $footer->attributes->merge(['class' => 'sidebar-custom d-flex', 'style' => 'height: 2rem']) }}>
            {{ $footer }}
        </div>
    @endif
</aside>
