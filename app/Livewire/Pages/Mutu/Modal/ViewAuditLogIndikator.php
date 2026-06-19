<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\IndicatorAuditLog;
use App\Models\Quality\QualityIndicatorRecord;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class ViewAuditLogIndikator extends Component
{
    use FlashComponent;

    /** @var Collection<int, IndicatorAuditLog>|null */
    public $logs;

    protected $listeners = ['view-audit-log' => 'loadLogs'];

    public function loadLogs(int $indicatorId, string $date): void
    {
        $record = QualityIndicatorRecord::where('indicator_id', $indicatorId)
            ->where('recorded_date', $date)
            ->first();

        if (! $record) {
            $this->flashError('Data tidak ditemukan.');

            return;
        }

        $this->logs = $record->auditLogs()->orderBy('created_at', 'asc')->get();

        $this->dispatchBrowserEvent('open-modal', ['id' => 'modal-view-audit-log-indikator']);
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.view-audit-log-indikator');
    }
}
