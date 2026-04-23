<?php

namespace App\Application\Ticket\Events;

use App\Models\Helpdesk\Ticket;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class TicketSlaBreached
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Ticket $ticket;

    public function __construct(
        Ticket $ticket
    ) {
        $this->ticket = $ticket;
    }
}
