<div wire:poll.30s>
    <style>
        .notification-sidebar {
            position: fixed;
            right: -300px;
            top: 0;
            width: 300px;
            height: 100%;
            background-color: white;
            border: 1px solid #dee2e6;
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
            padding: 4px;
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

    <li class="nav-item">
        <a class="nav-link" href="#" id="notification-icon">
            <i class="far fa-bell"></i>
            <sp class="badge badge-warning navbar-badge">{{ $this->unreadNotificationsCount }}</sp>
        </a>
    </li>

    <div id="notification-sidebar" class="notification-sidebar">
        <div class="sidebar-header">
            <h4>Notifications</h4>
            <button id="close-sidebar" class="close-sidebar">&times;</button>
        </div>
        <div class="sidebar-content">
            @foreach ($this->notifications as $notification)
                <div class="alert bg-olive">
                    <p>{{ $notification->data['message'] }}</p>
                    @php
                        preg_match('/filename="?([^"\s]+)"?/', $notification->data['file'], $matches);
                        $filename = $matches[1] ?? '';
                    @endphp
                    <button wire:click="download('{{ $filename }}')" class="btn btn-link">Download File</button>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        document.getElementById('notification-icon').addEventListener('click', function() {
            document.getElementById('notification-sidebar').classList.toggle('open');
        });

        document.getElementById('close-sidebar').addEventListener('click', function() {
            document.getElementById('notification-sidebar').classList.remove('open');
        });
    </script>
</div>
