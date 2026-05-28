<?php

namespace App\Application\Ticket\Actions;

use App\Application\Ticket\DTOs\AssignTicketData;
use App\Application\Ticket\Events\TicketAssigned;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Models\Aplikasi\User;
use App\Models\Helpdesk\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final class AssignTicketAction
{
    private TicketRepositoryInterface $repository;

    public function __construct(TicketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(AssignTicketData $data): Ticket
    {
        return DB::transaction(function () use ($data): Ticket {

            $ticket = $this->repository->findById($data->ticketId);

            if (! $ticket) {
                throw new \RuntimeException("Ticket #{$data->ticketId} tidak ditemukan.");
            }

            $isReassignment = ! is_null($ticket->assignee_id);
            $previousAssigneeId = $ticket->assignee_id;

            $previousAssigneeName = $ticket->assignee ? $ticket->assignee->name : ($previousAssigneeId ? "#$previousAssigneeId" : null);

            $ticket->assignee_id = $data->assigneeId;
            $ticket->assigned_at = now();

            if ($ticket->status === Ticket::STATUS_OPEN) {
                $ticket->status = Ticket::STATUS_PROGRESS;

                if (! $ticket->first_responded_at) {
                    $ticket->first_responded_at = now();
                }
            }

            $this->repository->save($ticket);

            $assignee = User::find($data->assigneeId);
            $assignedBy = User::find($data->assignedById);

            $ticket->activities()->create([
                'causer_id'   => $data->assignedById,
                'type'        => 'assigned',
                'description' => $isReassignment
                    ? "Re-assign dari {$previousAssigneeName} ke ".($assignee->name ?? 'Teknisi')
                    : 'Di-assign ke '.($assignee->name ?? 'Teknisi'),
                'old_value'   => $previousAssigneeId ? (string) $previousAssigneeId : null,
                'new_value'   => (string) $data->assigneeId,
                'created_at'  => now(),
            ]);

            Event::dispatch(new TicketAssigned(
                $ticket->fresh(),
                $assignee ? $assignee->nik : $data->assigneeId,
                $assignedBy ? $assignedBy->nik : $data->assignedById,
                $isReassignment
            ));

            return $ticket->load(['assignee', 'activities']);
        });
    }
}
