<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Application\Quality\Actions\SaveQualityIndicatorRecordAction;
use App\Application\Quality\DTOs\QualityIndicatorRecordData;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\QualityIndicator;
use Illuminate\View\View;
use Livewire\Component;

class InputRecordIndikator extends Component
{
    use FlashComponent;

    public $indicatorId;

    public $indicatorName;

    public $recordedDate;

    public $numeratorValue = 0;

    public $denominatorValue = 0;

    public $notes;

    protected $listeners = ['input-record' => 'loadIndicator'];

    public function loadIndicator(int $id): void
    {
        $indicator = QualityIndicator::findOrFail($id);

        $this->indicatorId = $indicator->id;
        $this->indicatorName = $indicator->name;
        $this->recordedDate = now()->format('Y-m-d');

        $this->dispatchBrowserEvent('open-modal', ['id' => 'modal-input-record-indikator']);
    }

    public function save(SaveQualityIndicatorRecordAction $action): void
    {
        $data = QualityIndicatorRecordData::from([
            'indicator_id'      => $this->indicatorId,
            'recorded_date'     => $this->recordedDate,
            'numerator_value'   => $this->numeratorValue,
            'denominator_value' => $this->denominatorValue,
            'notes'             => $this->notes,
        ]);

        $action->execute($data);

        $this->flashSuccess('Penilaian harian berhasil disimpan.');
        $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-input-record-indikator']);
        $this->emit('record-saved');
        $this->reset(['numeratorValue', 'denominatorValue', 'notes']);
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-record-indikator');
    }
}
