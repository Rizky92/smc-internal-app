<?php

namespace App\Application\Ticket\Events;

use App\Models\Helpdesk\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Ticket $ticket;

    /** @var string|null NIK/NRP Pelapor */
    public ?string $nik;

    public function __construct(
        Ticket $ticket,
        ?string $nik
    ) {
        $this->ticket = $ticket;
        $this->nik = $nik;
    }
}
