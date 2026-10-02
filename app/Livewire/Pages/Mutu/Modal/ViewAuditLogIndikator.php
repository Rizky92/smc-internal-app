<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\IndicatorAuditLog;
use App\Models\Quality\QualityIndicatorRecord;
use App\Models\Quality\QualityIndicatorRecordHistory;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class ViewAuditLogIndikator extends Component
{
    use FlashComponent;

    /** @var Collection<int, IndicatorAuditLog>|null */
    public $logs;

    /** @var Collection<int, QualityIndicatorRecordHistory>|null */
    public $histories;

    protected $listeners = ['view-audit-log' => 'loadLogs'];

    public function loadLogs(int $indicatorId, string $date): void
    {
        $record = QualityIndicatorRecord::tanggal($indicatorId, $date)->first();

        if (! $record) {
            $this->flashError('Data tidak ditemukan.');

            return;
        }

        $this->logs = $record->auditLogs()->orderBy('created_at', 'asc')->get();
        $this->histories = $record->histories()->orderBy('created_at')->orderBy('id')->get();

        $this->dispatchBrowserEvent('open-modal', ['id' => 'modal-view-audit-log-indikator']);
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.view-audit-log-indikator');
    }
}
