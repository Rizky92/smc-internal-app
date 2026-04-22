<?php

namespace App\Application\Ticket\Events;

use App\Models\Aplikasi\User;
use App\Models\Helpdesk\Ticket;

final class TicketCreated
{
    public Ticket $ticket;

    public ?User $createdBy;

    public function __construct(
        Ticket $ticket,
        ?User $createdBy
    ) {
        $this->ticket = $ticket;
        $this->createdBy = $createdBy;
    }
}
