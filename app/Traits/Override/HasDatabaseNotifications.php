<?php

namespace App\Traits\Override;

use App\Models\Override\MultiConnectionDatabaseNotification;

trait HasDatabaseNotifications
{
    public function notifications()
    {
        return $this->morphMany(MultiConnectionDatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    public function readNotifications()
    {
        return $this->notifications()->whereNotNull('read_at');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
}