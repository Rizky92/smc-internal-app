<?php

namespace App\Notifications;

use App\Models\Override\MultiConnectionDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExportFailedNotification extends Notification
{
    use Queueable;

    private $user;

    private $filePath;

    public function __construct($user, $filePath)
    {
        $this->user = $user;
        $this->filePath = $filePath;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => $this->ensureUtf8('Export failed'),
            'user'    => $this->ensureUtf8($this->user->nama),
            'file'    => $this->ensureUtf8($this->filePath) ?? null,
        ];
    }

    /**
     * @psalm-param 'Export failed' $value
     *
     * @return (mixed|string)[]|false|string
     *
     * @psalm-return array<mixed|string>|false|string
     */
    private function ensureUtf8(string $value)
    {
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    /**
     * Get the database notification model to be used by the notification.
     *
     * @return string
     */
    public function databaseNotificationModel()
    {
        return MultiConnectionDatabaseNotification::class;
    }
}
