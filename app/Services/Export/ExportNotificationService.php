<?php

namespace App\Services\Export;

use App\Models\Aplikasi\User;
use App\Notifications\ExportFailedNotification;
use App\Notifications\ExportReadyNotification;
use Illuminate\Support\Facades\Notification;

class ExportNotificationService
{
    private string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    public function notifyReady(string $filePath): void
    {
        $user = User::findByNRP($this->userId);
        Notification::send($user, new ExportReadyNotification($user, $filePath));
    }

    public function notifyFailed(): void
    {
        $user = User::findByNRP($this->userId);
        Notification::send($user, new ExportFailedNotification($user));
    }
}
