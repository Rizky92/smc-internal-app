<li class="nav-item">
    <a class="nav-link" href="#" id="notification-icon">
        <i class="far fa-bell"></i>
        <span class="badge badge-warning navbar-badge">15</span>
    </a>
</li>

<div id="notification-sidebar" class="notification-sidebar">
    <div class="sidebar-header">
        <h4>Notifications</h4>
        <button id="close-sidebar" class="close-sidebar">&times;</button>
    </div>
    <div class="sidebar-content">
        
        {{-- @dd(auth()->user()->load('notifications')->notifications) --}}
        
        @foreach(auth()->user()->load('notifications')->notifications as $notification)
            <div class="alert alert-info">
                {{-- <strong>{{ $notification->data['title'] }}</strong> --}}
                <p>{{ $notification->data['message'] }}</p>
            </div>
        @endforeach
    </div>
</div>

<style>
.notification-sidebar {
    position: fixed;
    right: -300px;
    top: 0;
    width: 300px;
    height: 100%;
    background-color: white;
    box-shadow: -2px 0 5px rgba(0,0,0,0.5);
    transition: right 0.3s;
    z-index: 1050;
}

.notification-sidebar.open {
    right: 0;
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.sidebar-content {
    padding: 10px;
}

.close-sidebar {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
}
</style>

<script>
document.getElementById('notification-icon').addEventListener('click', function() {
    document.getElementById('notification-sidebar').classList.toggle('open');
});

document.getElementById('close-sidebar').addEventListener('click', function() {
    document.getElementById('notification-sidebar').classList.remove('open');
});
</script>