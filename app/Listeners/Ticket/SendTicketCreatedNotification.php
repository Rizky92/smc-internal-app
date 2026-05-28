<?php

namespace App\Listeners\Ticket;

use App\Application\Ticket\Events\TicketCreated;
use App\Domain\Ticket\Enums\TicketPriority;
use App\Infrastructure\External\TelegramService;
use App\Models\Aplikasi\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTicketCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    private TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Handle the event.
     */
    public function handle(TicketCreated $event): void
    {
        $ticket = $event->ticket;
        $priorityLabel = TicketPriority::label($ticket->priority);
        $unitName = $ticket->department->nama ?? '-';

        // Ambil data user via NIK/NRP (lebih aman untuk Queue JSON)
        $creator = $event->nik ? User::findByNRP($event->nik) : null;
        $reporter = $ticket->reporter_display_name;

        $message = "🚨 *TIKET IT BARU!* 🚨\n\n"
            ."📌 *Nomor:* #{$ticket->ticket_number}\n"
            ."📝 *Judul:* {$ticket->title}\n"
            ."🏢 *Unit:* {$unitName}\n"
            ."⚠️ *Prioritas:* {$priorityLabel}\n"
            ."👤 *Pelapor:* {$reporter}\n\n"
            .'🔗 [Klik untuk Proses Tiket]('.route('admin.form-it.detail', $ticket->id).')';

        $success = $this->telegram->sendMessage($message);

        if (! $success) {
            Log::error("Gagal mengirim notifikasi Telegram untuk tiket #{$ticket->ticket_number}");
        }
    }
}
