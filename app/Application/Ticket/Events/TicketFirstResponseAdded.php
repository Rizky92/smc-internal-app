<?php

namespace App\Application\Ticket\Events;

use App\Models\Aplikasi\User;
use App\Models\Helpdesk\Ticket;

final class TicketFirstResponseAdded
{
    public Ticket $ticket;

    public User $respondedBy;

    public function __construct(
        Ticket $ticket,
        User $respondedBy
    ) {
        $this->ticket = $ticket;
        $this->respondedBy = $respondedBy;
    }
}
