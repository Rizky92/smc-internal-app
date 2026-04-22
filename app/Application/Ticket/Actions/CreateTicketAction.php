<?php

namespace App\Application\Ticket\Actions;

use App\Application\Ticket\DTOs\CreateTicketData;
use App\Application\Ticket\Events\TicketCreated;
use App\Application\Ticket\Services\SlaCalculator;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Models\Aplikasi\User;
use App\Models\Helpdesk\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

/**
 * CreateTicketAction — satu use case, satu tanggung jawab.
 */
final class CreateTicketAction
{
    private TicketRepositoryInterface $repository;

    private SlaCalculator $slaCalculator;

    public function __construct(
        TicketRepositoryInterface $repository,
        SlaCalculator $slaCalculator
    ) {
        $this->repository = $repository;
        $this->slaCalculator = $slaCalculator;
    }

    public function execute(CreateTicketData $data): Ticket
    {
        return DB::transaction(function () use ($data): Ticket {

            // 1. Hitung SLA berdasarkan priority dan waktu sekarang
            $slaDeadline = $this->slaCalculator->calculate(
                $data->priority,
                now()
            );

            // 2. Bangun model Ticket (belum disimpan)
            $ticket = new Ticket([
                'ticket_number'      => Ticket::generateTicketNumber(),
                'title'              => $data->title,
                'description'        => $data->description,
                'category_id'        => $data->categoryId,
                'priority'           => $data->priority,
                'status'             => Ticket::STATUS_OPEN,
                'department_id'      => $data->departmentId,
                'location'           => $data->location,
                'reporter_id'        => $data->reporterId,
                'reporter_name'      => $data->reporterName,
                'reporter_phone'     => $data->reporterPhone,
                'assignee_id'        => $data->assigneeId,
                'created_by'         => $data->createdBy,
                'sla_due_at'         => $slaDeadline->resolutionDue,
                'sla_response_due_at'=> $slaDeadline->responseDue,
                'assigned_at'        => $data->assigneeId ? now() : null,
            ]);

            // 3. Simpan via repository
            $ticket = $this->repository->save($ticket);

            // 4. Tangani Lampiran
            if (! empty($data->attachments)) {
                foreach ($data->attachments as $file) {
                    $path = $file->store('tickets/attachments', 'public');

                    $ticket->attachments()->create([
                        'id_user'       => $data->createdBy,
                        'original_name' => $file->getClientOriginalName(),
                        'path'          => $path,
                        'disk'          => 'public',
                        'size'          => $file->getSize(),
                        'mime_type'     => $file->getMimeType(),
                        'created_at'    => now(),
                    ]);
                }
            }

            // 5. Catat activity log
            $ticket->activities()->create([
                'causer_id'   => $data->createdBy,
                'type'        => 'created',
                'description' => 'Tiket dibuat',
                'new_value'   => $ticket->ticket_number,
                'created_at'  => now(),
            ]);

            // 6. Jika langsung di-assign, catat activity assign juga
            if ($data->assigneeId) {
                $ticket->activities()->create([
                    'causer_id'   => $data->createdBy,
                    'type'        => 'assigned',
                    'description' => 'Langsung di-assign saat pembuatan tiket',
                    'new_value'   => (string) $data->assigneeId,
                    'created_at'  => now(),
                ]);
            }

            // 7. Dispatch domain event — listener akan kirim notifikasi
            Event::dispatch(new TicketCreated(
                $ticket,
                $data->createdBy ? User::find($data->createdBy) : null
            ));

            return $ticket->load(['category', 'department', 'assignee', 'reporter', 'attachments']);
        });
    }
}
