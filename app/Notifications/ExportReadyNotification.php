<?php

namespace App\Notifications;

use App\Models\Override\MultiConnectionDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExportReadyNotification extends Notification
{
    use Queueable;

    private ?string $filePath;

    private string $message;

    private string $status;

    /**
     * @param  array{
     *      filePath: string|null,
     *      message: string,
     *      status: string
     *  }  $params
     */
    public function __construct(array $params)
    {
        $this->filePath = $params['filePath'];
        $this->message = $params['message'];
        $this->status = $params['status'];
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
            'message' => $this->ensureUtf8($this->message),
            'file'    => $this->ensureUtf8($this->filePath),
            'status'  => 'success',
        ];
    }

    /**
     * @psalm-param 'Export data is ready for download' $value
     *
     * @return false|null|string
     *
     * @psalm-return array<mixed|string>|false|string
     */
    private function ensureUtf8(?string $value)
    {
        if ($value === null) {
            return null;
        }

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
