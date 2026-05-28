<?php

namespace App\Application\Ticket\DTOs;

final class UpdateStatusData
{
    public int $ticketId;

    public string $newStatus;

    public string $changedById;

    public ?string $note;

    public function __construct(
        int $ticketId,
        string $newStatus,
        string $changedById,
        ?string $note = null
    ) {
        $this->ticketId = $ticketId;
        $this->newStatus = $newStatus;
        $this->changedById = $changedById;
        $this->note = $note;
    }
}
