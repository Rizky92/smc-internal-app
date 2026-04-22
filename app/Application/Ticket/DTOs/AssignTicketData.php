<?php

namespace App\Application\Ticket\DTOs;

final class AssignTicketData
{
    public int $ticketId;

    public string $assigneeId;

    public string $assignedById;

    public function __construct(
        int $ticketId,
        string $assigneeId,
        string $assignedById
    ) {
        $this->ticketId = $ticketId;
        $this->assigneeId = $assigneeId;
        $this->assignedById = $assignedById;
    }
}
