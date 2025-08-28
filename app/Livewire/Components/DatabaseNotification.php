<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class DatabaseNotification extends Component
{
    public string $notificationId;

    public bool $isSidebarOpen = false;

    protected $listeners = [
        'toggleSidebar' => 'toggleSidebar',
    ];

    public function toggleSidebar()
    {
        $this->isSidebarOpen = !$this->isSidebarOpen;
    }

    public function render()
    {
        return view('livewire.components.database-notification');
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

    public function download(string $filePath): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::download("$filePath");
    }
}