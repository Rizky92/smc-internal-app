<div>
    @once
        @push('css')
            <link href="{{ asset('css/fontawesome5-all.min.css') }}" rel="stylesheet">
        @endpush
    @endonce
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
        <div class="sidebar-content" @if ($isSidebarOpen) wire:poll.5s @endif>
            @if ($this->unreadNotificationsCount > 0)
                <button wire:click="markAllAsRead" class="btn btn-link">Tandai semua sudah dibaca</button>
            @elseif ($this->notifications->count() > 0)
                <button wire:click="clearAll" class="btn btn-link">Hapus semua</button>
            @endif

            @forelse ($this->notifications as $notification)
                <div class="sidebar-item">
                    <div class="d-flex p-2">
                        <div class="">
                            <i class="far fa-check-circle fa-lg" style="color: #3d9970"></i>
                        </div>
                        <div class="">
                            <p class="my-0 ml-2">{{ $notification->data['message'] }}</p>
                            <p class="p-2">{{ $notification->created_at->diffForHumans() }}</p>
                            <div class="d-flex">
                                @php
                                    $filePath = $notification->data['file'];
                                @endphp
                                <button wire:click="download('{{ $filePath }}')" class="btn btn-link">Download</button>
                                <button wire:click="markAsRead('{{ $notification->id }}')"  wire:key="{{ $notification->id }}" class="btn btn-link">Tandai sudah dibaca</button>
                            </div>
                        </div>
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
        document.getElementById('notification-icon').addEventListener('click', function(event) {
            event.preventDefault();
            window.livewire.emit('toggleSidebar');
        });

        document.getElementById('close-sidebar').addEventListener('click', function(event) {
            event.preventDefault();
            window.livewire.emit('toggleSidebar');
        });
    </script>
</div>
