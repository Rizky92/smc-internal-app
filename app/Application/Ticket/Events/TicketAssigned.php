<?php

namespace App\Application\Ticket\Events;

use App\Models\Aplikasi\User;
use App\Models\Helpdesk\Ticket;

final class TicketAssigned
{
    public Ticket $ticket;

    public User $assignee;

    public ?User $assignedBy;

    public bool $isReassignment = false;

    public function __construct(
        Ticket $ticket,
        User $assignee,
        ?User $assignedBy = null,
        bool $isReassignment = false
    ) {
        $this->ticket = $ticket;
        $this->assignee = $assignee;
        $this->assignedBy = $assignedBy;
        $this->isReassignment = $isReassignment;
    }
}
