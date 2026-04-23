<?php

namespace App\Application\Ticket\Events;

use App\Models\Helpdesk\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketStatusChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Ticket $ticket;

    public string $oldStatus;

    public string $newStatus;

    public ?string $nik;

    public function __construct(
        Ticket $ticket,
        string $oldStatus,
        string $newStatus,
        ?string $nik
    ) {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->nik = $nik;
    }
}
