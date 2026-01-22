<?php

namespace App\Notifications;

use App\Models\Override\MultiConnectionDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportReadyNotification extends Notification
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Export Completed')
            ->line('Your export is complete. You can download the file using the link below.')
            ->action('Download File', url($this->filePath))
            ->line('Thank you for using our application!');
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
            'message' => $this->ensureUtf8('Export data is ready for download'),
            'user'    => $this->ensureUtf8($this->user->nama),
            'file'    => $this->ensureUtf8($this->filePath),
        ];
    }

    /**
     * @psalm-param 'Export data is ready for download' $value
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
