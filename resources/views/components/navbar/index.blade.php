<nav class="main-header navbar navbar-expand navbar-light bg-white text-sm">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
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
                <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: hidden">@csrf</form>
                <button type="submit" form="logout-form" class="btn btn-outline-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="ml-2">Logout</span>
                </button>
            </li>
        @endauth
    </ul>
</nav>
