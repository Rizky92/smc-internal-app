<?php

namespace App\Livewire\Pages\It;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Helpdesk\Ticket;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class FormIt extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    /** @var string */
    public $status;

    /** @var string */
    public $priority;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
            'status'   => ['except' => ''],
            'priority' => ['except' => ''],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return Ticket::query()
            ->with([
                'category',
                'department',
                'reporter',
                'assignee',
                'createdBy',
                'attachments',
            ])
            ->when($this->cari, fn ($q) => $q->search($this->cari))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->priority, fn ($q) => $q->where('priority', $this->priority))
            ->when($this->tglAwal && $this->tglAkhir, fn ($q) => $q->whereBetween('created_at', [$this->tglAwal, $this->tglAkhir]))
            ->orderByDesc('created_at')
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.it.form-it')
            ->layout(BaseLayout::class, ['title' => 'Form It']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->status = '';
        $this->priority = '';
    }

    protected function getSortColumns(): array
    {
        return ['ticket_number', 'title', 'priority', 'status', 'created_at'];
    }

    protected function dataPerSheet(): array
    {
        return [
            'Tiket' => $this->collection?->items() ?? [],
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Tiket'  => 'ticket_number',
            'Judul'      => 'title',
            'Kategori'   => fn ($row) => $row->category->name ?? '-',
            'Departemen' => fn ($row) => $row->department->nama ?? '-',
            'Prioritas'  => 'priority',
            'Status'     => 'status',
            'PIC IT'     => fn ($row) => $row->assignee->nama ?? '-',
            'Pelapor'    => 'reporter_name',
            'Dibuat'     => 'created_at',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'Laporan Tiket IT',
            'Periode: '.$this->tglAwal.' - '.$this->tglAkhir,
        ];
    }

    /**
     * @return string[]
     *
     * @psalm-return array{open: 'Open', progress: 'In Progress', waiting: 'Menunggu Konfirmasi', resolved: 'Resolved', closed: 'Closed'}
     */
    public function getStatusOptionsProperty(): array
    {
        return Ticket::STATUS_LABELS;
    }

    /**
     * @return string[]
     *
     * @psalm-return array{critical: 'Critical', high: 'High', medium: 'Medium', low: 'Low'}
     */
    public function getPriorityOptionsProperty(): array
    {
        return Ticket::PRIORITY_LABELS;
    }
}
