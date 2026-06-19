<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\QualityIndicatorRecord;
use Illuminate\View\View;
use Livewire\Component;

class InputKoreksiIndikator extends Component
{
    use FlashComponent;

    public $indicatorId;

    public $recordedDate;

    public $numeratorValue;

    public $denominatorValue;

    public $notes;

    public $reason;

    protected $listeners = ['koreksi-record' => 'loadRecord'];

    protected function rules(): array
    {
        return [
            'numeratorValue'   => ['required', 'integer', 'min:0'],
            'denominatorValue' => ['required', 'integer', 'min:0'],
            'notes'            => ['nullable', 'string'],
            'reason'           => ['required', 'string', 'min:3'],
        ];
    }

    public function loadRecord(int $indicatorId, string $date): void
    {
        $this->resetExcept([]);

        $record = QualityIndicatorRecord::where('indicator_id', $indicatorId)
            ->where('recorded_date', $date)
            ->firstOrFail();

        $this->indicatorId = $indicatorId;
        $this->recordedDate = $date;
        $this->numeratorValue = $record->numerator_value;
        $this->denominatorValue = $record->denominator_value;
        $this->notes = $record->notes;
        $this->reason = '';

        $this->dispatchBrowserEvent('open-modal', ['id' => 'modal-input-koreksi-indikator']);
    }

    public function save(): void
    {
        if (! auth()->user()->can('mutu.validasi-data.approve')) {
            $this->flashError('Anda tidak memiliki akses untuk melakukan koreksi.');

            return;
        }

        $this->validate();

        $record = QualityIndicatorRecord::where('indicator_id', $this->indicatorId)
            ->where('recorded_date', $this->recordedDate)
            ->firstOrFail();

        $changes = [];
        $oldNumerator = $record->numerator_value;
        $oldDenominator = $record->denominator_value;
        $oldNotes = $record->notes;

        if ((int) $this->numeratorValue !== $oldNumerator) {
            $changes[] = [
                'field_name' => 'numerator_value',
                'old_value'  => (string) $oldNumerator,
                'new_value'  => (string) $this->numeratorValue,
            ];
        }

        if ((int) $this->denominatorValue !== $oldDenominator) {
            $changes[] = [
                'field_name' => 'denominator_value',
                'old_value'  => (string) $oldDenominator,
                'new_value'  => (string) $this->denominatorValue,
            ];
        }

        if ((string) $this->notes !== (string) $oldNotes) {
            $changes[] = [
                'field_name' => 'notes',
                'old_value'  => (string) $oldNotes,
                'new_value'  => (string) $this->notes,
            ];
        }

        $record->update([
            'numerator_value'   => $this->numeratorValue,
            'denominator_value' => $this->denominatorValue,
            'notes'             => $this->notes,
            'status'            => 'approved_with_correction',
        ]);

        foreach ($changes as $change) {
            $record->auditLogs()->create([
                'field_name'    => $change['field_name'],
                'old_value'     => $change['old_value'],
                'new_value'     => $change['new_value'],
                'changed_by'    => user()->nik,
                'reason'        => $this->reason,
                'recorded_date' => $this->recordedDate,
            ]);
        }

        $this->flashSuccess('Data berhasil dikoreksi dan disetujui.');
        $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-input-koreksi-indikator']);
        $this->emit('record-saved');
        $this->reset();
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-koreksi-indikator');
    }
}
