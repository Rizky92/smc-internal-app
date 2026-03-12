<?php

namespace App\Notifications;

use App\Models\Override\MultiConnectionDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class Notification extends BaseNotification
{
    use Queueable;

    private string $message;

    private string $status = 'info';

    private ?string $filePath = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct() {}

    public static function make(): self
    {
        return new static;
    }

    public function message(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function filePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function success(): self
    {
        $this->status = 'success';

        return $this;
    }

    public function warning(): self
    {
        $this->status = 'warning';

        return $this;
    }

    public function danger(): self
    {
        $this->status = 'danger';

        return $this;
    }

    public function info(): self
    {
        $this->status = 'info';

        return $this;
    }

    public function send(?Model $notifiable): void
    {
        NotificationFacade::send($notifiable, $this);
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
     */
    public function toArray($notifiable): array
    {
        return [
            'message'  => $this->ensureUtf8($this->message),
            'status'   => $this->ensureUtf8($this->status),
            'filePath' => $this->ensureUtf8($this->filePath),
        ];
    }

    private function ensureUtf8(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    public function databaseNotificationModel(): string
    {
        return MultiConnectionDatabaseNotification::class;
    }
}
