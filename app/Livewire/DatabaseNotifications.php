<?php

namespace App\Livewire;

use App\Models\Aplikasi\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\View\View;

class DatabaseNotifications extends Component
{

    /** @var string */
    public $notificationId;

    public function render(): View
    {
        return view('livewire.database-notifications');
    }

    public function getNotificationsProperty(): DatabaseNotificationCollection
    {
        return auth()->user()->notifications()->latest()->take(3)->get();
    }

    public function getUnreadNotificationsCountProperty(): int
    {
        return auth()->user()->unreadNotifications->count();
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

    public function download($file)
    {
        $path = Storage::disk('public')->path('excel/' . $file);

        if (!Storage::disk('public')->exists('excel/' . $file)) {
            session()->flash('error', 'File not found.');
            return;
        }
    
        return response()->download($path);
    }
}
