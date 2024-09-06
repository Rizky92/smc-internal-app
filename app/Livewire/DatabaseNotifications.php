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
    public function render(): View
    {
        return view('livewire.database-notifications');
    }

    public function getNotificationsProperty(): DatabaseNotificationCollection
    {
        return auth()->user()->notifications;
    }

    public function getUnreadNotificationsCountProperty(): int
    {
        return auth()->user()->unreadNotifications->count();
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
