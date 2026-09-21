<?php

namespace App\Livewire\Components;

use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseNotification extends Component
{
    public string $notificationId;

    public bool $isSidebarOpen = false;

    #[On('toggleSidebar')]
    public function toggleSidebar(): void
    {
        $opening = ! $this->isSidebarOpen;
        $this->isSidebarOpen = $opening;

        if ($opening) {
            $this->markAllAsRead();
        }
    }

    public function render(): View
    {
        return view('livewire.components.database-notification');
    }

    public function getNotificationsProperty()
    {
        if (! auth()->check()) {
            return collect();
        }

        return auth()->user()->notifications()->get();
    }

    public function getUnreadNotificationsCountProperty()
    {
        if (! auth()->check()) {
            return 0;
        }

        return auth()->user()->unreadNotifications()->count();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function clearAll(): void
    {
        auth()->user()->notifications()->delete();
    }

    public function clear(string $notificationId): void
    {
        auth()->user()->notifications()->where('id', $notificationId)->first()->delete();
    }

    public function download(string $filePath): ?StreamedResponse
    {
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        }

        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->download($filePath);
        }

        $this->dispatch('flash.error', 'File tidak ditemukan!');

        return null;
    }
}
