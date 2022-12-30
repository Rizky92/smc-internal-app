<nav class="main-header navbar navbar-expand navbar-light bg-light text-sm border-bottom-0">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item">
            <ul class="d-flex justify-content-start align-items-center nav-link" style="list-style: none">
                <li class="text-muted">Dashboard</li>
                <li class="mx-3">/</li>
                <li class="text-muted">Farmasi</li>
                <li class="mx-3">/</li>
                <li class="text-dark font-weight-bold text-uppercase">Darurat Stok</li>
            </ul>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            @auth('web')    
                <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: hidden">@csrf</form>
                <button type="submit" form="logout-form" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="ml-2">Logout</span>
                </button>
            @endauth
        </li>
    </ul>
</nav>