<?php

namespace App\Application\Ticket\DTOs;

final class AddCommentData
{
    public int $ticketId;

    public string $userId;

    public string $content;

    public bool $isInternal;

    public function __construct(
        int $ticketId,
        string $userId,
        string $content,
        bool $isInternal = false
    ) {
        $this->ticketId = $ticketId;
        $this->userId = $userId;
        $this->content = $content;
        $this->isInternal = $isInternal;
    }
}
