<?php

namespace App\Livewire;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\View\View;

class DatabaseNotifications extends Component
{
     /**
     * The columns used for search query.
     *
     * @var string[]
     */    
    public $notifications = [];

    /**
     * The columns used for search query.
     *
     * @var int[]
     */
    public $unreadNotifications = [];

    /** @var mixed */
    protected $listeners = ['refreshNotifications' => 'loadNotifications'];

    public function mount(): void
    {
        //
    }

    public function getNotificationsProperty()
    {
        return auth()->user()->notifications;
    }

    public function render(): View
    {
        return view('livewire.database-notifications');
    }
}
