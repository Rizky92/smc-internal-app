<?php

namespace App\Application\Ticket\Actions;

use App\Application\Ticket\DTOs\UpdateStatusData;
use App\Application\Ticket\Events\TicketStatusChanged;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Models\Aplikasi\User;
use App\Models\Helpdesk\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;

/**
 * UpdateStatusAction — menangani transisi status tiket.
 */
final class UpdateStatusAction
{
    private TicketRepositoryInterface $repository;

    public function __construct(TicketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(UpdateStatusData $data): Ticket
    {
        return DB::transaction(function () use ($data): Ticket {

            $ticket = $this->repository->findById($data->ticketId);

            if (! $ticket) {
                throw new \RuntimeException("Ticket #{$data->ticketId} tidak ditemukan.");
            }

            $oldStatus = $ticket->status;
            $newStatus = $data->newStatus;

            // Guard: validasi transisi di domain layer
            if (! TicketStatus::canTransition($oldStatus, $newStatus)) {
                throw new InvalidArgumentException(
                    'Transisi status dari ['.TicketStatus::label($oldStatus).'] ke ['.TicketStatus::label($newStatus).'] tidak diizinkan.'
                );
            }

            // Update status + lifecycle timestamps
            $ticket->status = $newStatus;
            $this->applyLifecycleTimestamps($ticket, $newStatus);

            $this->repository->save($ticket);

            // Catat activity log
            $ticket->activities()->create([
                'causer_id'   => $data->changedById,
                'type'        => 'status_changed',
                'description' => $data->note
                    ?? 'Status diubah ke '.TicketStatus::label($newStatus),
                'old_value'   => $oldStatus,
                'new_value'   => $newStatus,
                'created_at'  => now(),
            ]);

            // Catatan opsional dari teknisi
            if ($data->note) {
                $ticket->comments()->create([
                    'id_user'     => $data->changedById,
                    'content'     => $data->note,
                    'is_internal' => true,
                ]);
            }

            // Dispatch domain event
            Event::dispatch(new TicketStatusChanged(
                $ticket->fresh(),
                $oldStatus,
                $newStatus,
                User::find($data->changedById)
            ));

            return $ticket->load(['category', 'department', 'assignee', 'activities']);
        });
    }

    private function applyLifecycleTimestamps(Ticket $ticket, string $newStatus): void
    {
        switch ($newStatus) {
            case TicketStatus::Progress:
                $this->handleProgressTransition($ticket);
                break;
            case TicketStatus::Resolved:
                $ticket->resolved_at ??= now();
                break;
            case TicketStatus::Closed:
                $ticket->closed_at ??= now();
                break;
        }
    }

    private function handleProgressTransition(Ticket $ticket): void
    {
        // First response: catat hanya sekali (tidak overwrite jika sudah ada)
        if (! $ticket->first_responded_at) {
            $ticket->first_responded_at = now();

            // Cek apakah response SLA sudah breach
            if ($ticket->sla_response_due_at && now()->gt($ticket->sla_response_due_at)) {
                $ticket->activities()->create([
                    'causer_id'   => null,
                    'type'        => 'sla_breached',
                    'description' => 'SLA response time dilanggar',
                    'created_at'  => now(),
                ]);
            }
        }
    }
}
