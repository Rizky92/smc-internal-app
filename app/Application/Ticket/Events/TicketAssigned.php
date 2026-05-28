<?php

namespace App\Application\Ticket\Events;

use App\Models\Helpdesk\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketAssigned
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Ticket $ticket;

    public string $assigneeNik;

    public ?string $assignedByNik;

    public bool $isReassignment;

    public function __construct(
        Ticket $ticket,
        string $assigneeNik,
        ?string $assignedByNik = null,
        bool $isReassignment = false
    ) {
        $this->ticket = $ticket;
        $this->assigneeNik = $assigneeNik;
        $this->assignedByNik = $assignedByNik;
        $this->isReassignment = $isReassignment;
    }
}
