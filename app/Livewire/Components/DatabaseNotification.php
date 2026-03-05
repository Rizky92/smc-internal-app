<?php

namespace App\Livewire\Components;

use App\Models\ExportSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseNotification extends Component
{
    public string $notificationId;

    public bool $isSidebarOpen = false;

    protected $listeners = [
        'toggleSidebar' => 'toggleSidebar',
    ];

    public function toggleSidebar(): void
    {
        $this->isSidebarOpen = ! $this->isSidebarOpen;
    }

    public function render(): View
    {
        return view('livewire.components.database-notification', [
            'activeExportSessions' => ExportSession::where('user_id', auth()->user()->nik)
                ->whereNotIn('status', ['done', 'failed'])
                ->latest()
                ->get(),
        ]);
    }

    public function getNotificationsProperty()
    {
        return auth()->user()->notifications()->get();
    }

    public function getUnreadNotificationsCountProperty()
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function markAsRead(string $notificationId): void
    {
        auth()->user()->notifications()->where('id', $notificationId)->first()->markAsRead();
    }

    public function clearAll(): void
    {
        auth()->user()->notifications()->delete();
    }

    public function clear(string $notificationId): void
    {
        auth()->user()->notifications()->where('id', $notificationId)->first()->delete();
    }

    public function download(string $filePath): StreamedResponse
    {
        return Storage::download("$filePath");
    }
}
