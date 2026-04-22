<?php

namespace App\Application\Ticket\Events;

use App\Models\Helpdesk\Ticket;

final class TicketSlaBreached
{
    public Ticket $ticket;

    public function __construct(
        Ticket $ticket
    ) {
        $this->ticket = $ticket;
    }
}
