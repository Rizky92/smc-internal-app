<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            @auth('web')    
                <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: hidden">@csrf</form>
                <button type="submit" form="logout-form" class="btn btn-link nav-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="ml-2">Logout</span>
                </button>
            @endauth
        </li>
    </ul>
</nav>