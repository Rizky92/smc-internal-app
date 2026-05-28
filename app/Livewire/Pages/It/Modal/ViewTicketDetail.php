<?php

namespace App\Livewire\Pages\It\Modal;

use App\Application\Ticket\Actions\AddCommentAction;
use App\Application\Ticket\Actions\AssignTicketAction;
use App\Application\Ticket\Actions\UpdateStatusAction;
use App\Application\Ticket\DTOs\AddCommentData;
use App\Application\Ticket\DTOs\AssignTicketData;
use App\Application\Ticket\DTOs\UpdateStatusData;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Helpdesk\Ticket;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class ViewTicketDetail extends Component
{
    use FlashComponent;

    public ?int $ticketId = null;

    public ?Ticket $ticket = null;

    public bool $isModal = true;

    public string $commentContent = '';

    public bool $isInternal = false;

    public string $statusNote = '';

    protected $listeners = [
        'show' => 'show',
    ];

    public function mount(?int $ticketId = null): void
    {
        if ($ticketId) {
            $this->isModal = false;
            $this->ticketId = $ticketId;
            $this->loadTicket();
        }
    }

    public function render(): View
    {
        $view = $this->isModal
            ? 'livewire.pages.it.modal.view-ticket-detail'
            : 'livewire.pages.it.ticket-detail';

        return view($view)
            ->layout(BaseLayout::class, ['title' => 'Detail Tiket '.($this->ticket?->ticket_number ?? '')]);
    }

    public function show(int $ticketId): void
    {
        $this->isModal = true;
        $this->ticketId = $ticketId;
        $this->loadTicket();

        $this->emit('show-modal-detail');
    }

    private function loadTicket(): void
    {
        $this->ticket = Ticket::with([
            'category',
            'department',
            'reporter',
            'assignee',
            'createdBy',
            'activities.causer',
            'comments.user',
            'attachments',
        ])->findOrFail($this->ticketId);
    }

    public function addComment(AddCommentAction $action): void
    {
        $this->validate([
            'commentContent' => 'required|string',
        ]);

        $data = new AddCommentData(
            ticketId: $this->ticketId,
            userId: Auth::user()->id_user,
            content: $this->commentContent,
            isInternal: $this->isInternal
        );

        $action->execute($data);

        $this->commentContent = '';
        $this->flashSuccess('Komentar berhasil ditambahkan');
        $this->loadTicket();
    }

    public function assignToMe(AssignTicketAction $action): void
    {
        $data = new AssignTicketData(
            ticketId: $this->ticketId,
            assigneeId: Auth::user()->id_user,
            assignedById: Auth::user()->id_user
        );

        $action->execute($data);

        $this->flashSuccess('Tiket berhasil di-assign ke Anda');
        $this->loadTicket();
    }

    public function updateStatus(string $status, UpdateStatusAction $action): void
    {
        $data = new UpdateStatusData(
            ticketId: $this->ticketId,
            newStatus: $status,
            changedById: Auth::user()->id_user,
            note: $this->statusNote ?: null
        );

        try {
            $action->execute($data);
            $this->statusNote = '';
            $this->flashSuccess('Status tiket berhasil diperbarui');
            $this->loadTicket();
        } catch (\Exception $e) {
            $this->flashError($e->getMessage());
        }
    }
}
