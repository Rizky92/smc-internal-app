<?php

namespace App\Livewire\Pages\It\Modal;

use App\Application\Ticket\Actions\CreateTicketAction;
use App\Application\Ticket\DTOs\CreateTicketData;
use App\Models\Bidang;
use App\Models\Helpdesk\Ticket;
use App\Models\Helpdesk\TicketCategory;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class InputFormIt extends Component
{
    use WithFileUploads;

    public $title;

    public $description;

    public $category_id;

    public $priority = Ticket::PRIORITY_MEDIUM;

    public $department_id;

    public $location;

    public $reporter_name;

    public $reporter_phone;

    /** @var TemporaryUploadedFile[] */
    public $attachments = [];

    protected $listeners = [
        'show-modal' => 'showModal',
        'hide-modal' => 'hideModal',
    ];

    public function mount(): void
    {
        $this->prefillCurrentUser();
    }

    protected function prefillCurrentUser(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->reporter_name = $user->nama ?? null;
            $this->reporter_phone = $user->no_hp ?? null;
        }
    }

    protected function rules(): array
    {
        return [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'category_id'    => ['required', 'exists:ticket_categories,id'],
            'priority'       => ['required', 'in:'.implode(',', [Ticket::PRIORITY_CRITICAL, Ticket::PRIORITY_HIGH, Ticket::PRIORITY_MEDIUM, Ticket::PRIORITY_LOW])],
            'department_id'  => ['required', 'exists:bidang,id'],
            'location'       => ['nullable', 'string', 'max:255'],
            'reporter_name'  => ['nullable', 'string', 'max:255'],
            'reporter_phone' => ['nullable', 'string', 'max:20'],
            'attachments.*'  => ['nullable', 'file', 'max:10240'], // Max 10MB per file
        ];
    }

    public function render(): View
    {
        return view('livewire.pages.it.modal.input-form-it');
    }

    public function getCategoriesProperty(): array
    {
        return TicketCategory::active()
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getDepartmentsProperty(): array
    {
        return Bidang::orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }

    public function getPriorityOptionsProperty(): array
    {
        return Ticket::PRIORITY_LABELS;
    }

    public function save(): void
    {
        $this->validate();

        $user = auth()->user();

        $data = new CreateTicketData(
            title: $this->title,
            categoryId: $this->category_id,
            priority: $this->priority,
            departmentId: $this->department_id,
            description: $this->description,
            location: $this->location,
            reporterId: $user?->id_user,
            reporterName: $this->reporter_name,
            reporterPhone: $this->reporter_phone,
            createdBy: $user?->id_user,
            attachments: $this->attachments
        );

        $action = app(CreateTicketAction::class);
        $action->execute($data);

        $this->emit('flash.success', 'Tiket berhasil dibuat');

        $this->emit('data-saved');

        $this->reset(['title', 'description', 'category_id', 'priority', 'department_id', 'location', 'attachments']);
    }
}
