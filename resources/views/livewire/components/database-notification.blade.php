<div>
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

        .export-progress-item {
            border-left: 3px solid #17a2b8;
            padding: 10px 12px;
            background-color: #f8fdff;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.4s ease;
            background-color: #17a2b8;
        }

        .progress-bar.is-done {
            background-color: #3d9970;
        }

        .progress-bar.is-failed {
            background-color: #dc3545;
        }
    </style>

    <li class="nav-item">
        <a class="nav-link" href="#" id="notification-icon">
            <i class="far fa-bell"></i>
            @if ($this->unreadNotificationsCount > 0)
                <span class="badge badge-warning navbar-badge">{{ $this->unreadNotificationsCount }}</span>
            @endif
        </a>
    </li>

    <div id="notification-sidebar" class="notification-sidebar {{ $isSidebarOpen ? 'open' : '' }}">
        <div class="sidebar-header">
            <h4>Notifikasi</h4>
            <button id="close-sidebar" class="close-sidebar">&times;</button>
        </div>

        <div class="sidebar-content" @if ($isSidebarOpen) wire:poll.3s @endif>
            {{-- Active Export Progress --}}
            @foreach ($activeExportSessions as $session)
                <div class="export-progress-item" wire:key="session-{{ $session->id }}">
                    <div class="d-flex align-items-center mb-1">
                        <i class="far fa-file-excel fa-lg mr-2" style="color: #17a2b8"></i>
                        <span class="font-weight-bold" style="font-size: 0.85rem">Export Excel</span>
                        <span class="ml-auto text-muted" style="font-size: 0.75rem">{{ $session->progress_percentage }}%</span>
                    </div>

                    <div class="progress mb-1">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: {{ $session->progress_percentage }}%"></div>
                    </div>

                    <small class="text-muted">{{ $session->status_label }}</small>
                </div>
            @endforeach

            {{-- Regular Notifications --}}
            @if ($this->unreadNotificationsCount > 0)
                <button wire:click="markAllAsRead" class="btn btn-link">Tandai semua sudah dibaca</button>
            @elseif ($this->notifications->count() > 0)
                <button wire:click="clearAll" class="btn btn-link">Hapus semua</button>
            @endif

            @forelse ($this->notifications as $notification)
                @php
                    $filePath = $notification->data['file'] ?? null;
                    $status = $notification->data['status'] ?? 'info';
                    if ($status === 'success') {
                        $icon = 'fa-check-circle';
                        $color = '#3d9970';
                    } elseif ($status === 'error') {
                        $icon = 'fa-times-circle';
                        $color = '#dc3545';
                    } else {
                        $icon = 'fa-info-circle';
                        $color = '#17a2b8';
                    }
                @endphp

                <div class="sidebar-item" style="border-color: {{ $color }}" wire:key="notif-{{ $notification->id }}">
                    <div class="d-flex p-2">
                        <div class="d-flex">
                            <div style="width: 28px">
                                <i class="far {{ $icon }} fa-lg" style="color: {{ $color }}"></i>
                            </div>
                            <div style="flex: 1">
                                <p class="my-0 ml-2">{{ $notification->data['message'] }}</p>
                                <p class="p-2">{{ $notification->created_at->diffForHumans() }}</p>
                                <div class="d-flex">
                                    @if (! empty($filePath))
                                        <button wire:click="download('{{ $filePath }}')" class="btn btn-link">Download</button>
                                    @endif

                                    <button wire:click="markAsRead('{{ $notification->id }}')" class="btn btn-link">Tandai sudah dibaca</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                @if ($activeExportSessions->isEmpty())
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
                @endif
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
