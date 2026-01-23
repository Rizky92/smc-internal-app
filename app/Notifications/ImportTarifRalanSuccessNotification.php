<?php

namespace App\Notifications;

use App\Models\Override\MultiConnectionDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ImportTarifRalanSuccessNotification extends Notification
{
    use Queueable;

    private $user;

    private $message;

    private $status;

    private $file;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $message, string $status = 'success', ?string $file = null)
    {
        $this->user = $user;
        $this->message = $message;
        $this->status = $status;
        $this->file = $file;
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
            'user'    => $this->ensureUtf8($this->user->nama),
            'message' => $this->ensureUtf8($this->message),
            'status'  => $this->status ?? 'info',
            'file'    => $this->file ?? null,
        ];
    }

    /**
     * @return (mixed|string)[]|false|string
     *
     * @psalm-return array<mixed|string>|false|string
     */
    private function ensureUtf8($value)
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
