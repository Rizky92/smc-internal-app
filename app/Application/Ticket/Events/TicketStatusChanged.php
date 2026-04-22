<?php

namespace App\Application\Ticket\Events;

use App\Models\Aplikasi\User;
use App\Models\Helpdesk\Ticket;

final class TicketStatusChanged
{
    public Ticket $ticket;

    public string $oldStatus;

    public string $newStatus;

    public ?User $changedBy;

    public function __construct(
        Ticket $ticket,
        string $oldStatus,
        string $newStatus,
        ?User $changedBy
    ) {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
    }
}
