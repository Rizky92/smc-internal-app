<?php

namespace App\Application\Ticket\Actions;

use App\Application\Ticket\DTOs\AddCommentData;
use App\Application\Ticket\Events\TicketFirstResponseAdded;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Models\Aplikasi\User;
use App\Models\Helpdesk\TicketComment;
use Illuminate\Support\Facades\Event;

final class AddCommentAction
{
    private TicketRepositoryInterface $repository;

    public function __construct(TicketRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function execute(AddCommentData $data): TicketComment
    {
        $ticket = $this->repository->findById($data->ticketId);

        if (! $ticket) {
            throw new \RuntimeException("Ticket #{$data->ticketId} tidak ditemukan.");
        }

        $comment = $ticket->comments()->create([
            'id_user'     => $data->userId,
            'content'     => $data->content,
            'is_internal' => $data->isInternal,
        ]);

        // Komentar publik pertama dari teknisi = first response
        if (! $data->isInternal && ! $ticket->first_responded_at) {
            $user = User::find($data->userId);

            // Cek apakah user memiliki hak akses (bukan sekedar login)
            if ($user && $user->can('form-it.read')) {
                $ticket->first_responded_at = now();
                $this->repository->save($ticket);

                Event::dispatch(new TicketFirstResponseAdded(
                    $ticket->fresh(),
                    $user->nik
                ));
            }
        }

        // Tambah ke activity log jika catatan internal
        if ($data->isInternal) {
            $ticket->activities()->create([
                'causer_id'   => $data->userId,
                'type'        => 'note_added',
                'description' => 'Catatan internal ditambahkan',
                'created_at'  => now(),
            ]);
        }

        return $comment->load('user');
    }
}
