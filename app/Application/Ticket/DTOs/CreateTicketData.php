<?php

namespace App\Application\Ticket\DTOs;

final class CreateTicketData
{
    public string $title;

    public int $categoryId;

    public string $priority;

    public int $departmentId;

    public ?string $description;

    public ?string $location;

    public ?string $reporterId;

    public ?string $reporterName;

    public ?string $reporterPhone;

    public ?string $assigneeId;

    public ?string $createdBy;

    public ?array $attachments;

    public function __construct(
        string $title,
        int $categoryId,
        string $priority,
        int $departmentId,
        ?string $description = null,
        ?string $location = null,
        ?string $reporterId = null,
        ?string $reporterName = null,
        ?string $reporterPhone = null,
        ?string $assigneeId = null,
        ?string $createdBy = null,
        ?array $attachments = null
    ) {
        $this->title = $title;
        $this->categoryId = $categoryId;
        $this->priority = $priority;
        $this->departmentId = $departmentId;
        $this->description = $description;
        $this->location = $location;
        $this->reporterId = $reporterId;
        $this->reporterName = $reporterName;
        $this->reporterPhone = $reporterPhone;
        $this->assigneeId = $assigneeId;
        $this->createdBy = $createdBy;
        $this->attachments = $attachments;
    }

    /**
     * Buat dari array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'],
            (int) $data['category_id'],
            $data['priority'],
            (int) $data['department_id'],
            $data['description'] ?? null,
            $data['location'] ?? null,
            isset($data['reporter_id']) ? (string) $data['reporter_id'] : null,
            $data['reporter_name'] ?? null,
            $data['reporter_phone'] ?? null,
            isset($data['assignee_id']) ? (string) $data['assignee_id'] : null,
            isset($data['created_by']) ? (string) $data['created_by'] : null,
            $data['attachments'] ?? null
        );
    }
}
