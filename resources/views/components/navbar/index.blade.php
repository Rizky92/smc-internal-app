<nav class="main-header navbar navbar-expand navbar-light bg-white text-sm">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" style="margin-top: 0">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        {{ Breadcrumbs::render() }}
    </ul>

    <ul class="navbar-nav ml-auto">
        @impersonating
        <li class="nav-item">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.impersonate.leave') }}">
                <i class="fas fa-door-open"></i>
                <span class="ml-1">Keluar dari impersonasi</span>
            </a>
        </li>
        @endImpersonating
        @auth('web')
            <li class="nav-item ml-2">
                <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: hidden">
                    @csrf
                </form>
                <x-button type="submit" form="logout-form" variant="danger" size="sm" outline title="Logout" icon="fas fa-sign-out-alt" />
            </li>
        @endauth
    </ul>
</nav>
