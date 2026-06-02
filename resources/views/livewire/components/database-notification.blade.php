<div @auth @if ($isSidebarOpen) wire:poll.5s @else wire:poll.180s @endif @endauth>
    <style>
        .notification-sidebar {
            display: flex;
            flex-direction: column;
            position: fixed;
            right: -300px;
            top: 0;
            width: 300px;
            height: 100vh;
            background-color: white;
            border: 1px solid #dee2e6;
            transition: right 0.3s;
            z-index: 1050;
        }

        .notification-sidebar.open {
            right: 0;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .sidebar-item {
            border-style: solid;
            border-color: #3d9970;
            border-top-width: 1px;
            border-bottom-width: 0px;
            border-right-width: 0px;
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
            @if ($this->unreadNotificationsCount > 0)
                <sp class="badge badge-warning navbar-badge">{{ $this->unreadNotificationsCount }}</sp>
            @endif
        </a>
    </li>

    <div id="notification-sidebar" class="notification-sidebar {{ $isSidebarOpen ? 'open' : '' }}">
        <div class="sidebar-header">
            <h4>Notifikasi</h4>
            <button id="close-sidebar" class="close-sidebar">&times;</button>
        </div>
        <div class="sidebar-content">
            @if ($this->unreadNotificationsCount === 0 && $this->notifications->count() > 0)
                <button wire:click="clearAll" class="btn btn-link">Hapus semua</button>
            @endif

            @forelse ($this->notifications as $notification)
                @php
                    $filePath = $notification->data['file'] ?? null;
                    $status = $notification->data['status'] ?? 'info';
                    if ($status === 'success') {
                        $icon = 'fas fa-check-circle fa-lg';
                        $color = '#3d9970';
                    } elseif ($status === 'error') {
                        $icon = 'fas fa-times-circle fa-lg';
                        $color = '#dc3545';
                    } else {
                        $icon = 'fas fa-info-circle fa-lg';
                        $color = '#17a2b8';
                    }
                @endphp

                <div class="sidebar-item" style="border-color: {{ $color }}">
                    <div class="d-flex p-2 justify-content-between align-items-start">
                        <div class="d-flex">
                            <div style="width: 28px">
                                <i class="{{ $icon }}" style="color: {{ $color }}"></i>
                            </div>
                            <div style="flex: 1">
                                <p class="my-0 ml-2">{{ $notification->data['message'] }}</p>
                                <p class="p-2">{{ $notification->created_at->diffForHumans() }}</p>
                                <div class="d-flex">
                                    @if (! empty($filePath))
                                        <a href="{{ asset(Storage::url($filePath)) }}" class="btn btn-link" download>Download</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <button wire:click.prevent="clear('{{ $notification->id }}')" class="close" type="button" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-4 pt-4 d-flex flex-column">
                    <div class="mb-2 mt-2 d-flex justify-content-center">
                        <div class="p-3 bg-secondary rounded-circle">
                            <i class="far fa-bell-slash fa-lg"></i>
                        </div>
                    </div>
                    <div class="text-center">
                        <h4>Tidak ada notifikasi</h4>
                        <p>Anda tidak memiliki notifikasi baru</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        document.getElementById('notification-icon').addEventListener('click', function (event) {
            event.preventDefault();
            window.livewire.emit('toggleSidebar');
        });

        document.getElementById('close-sidebar').addEventListener('click', function (event) {
            event.preventDefault();
            window.livewire.emit('toggleSidebar');
        });
    </script>
</div>
