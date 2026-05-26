<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Application\Quality\Actions\DeleteQualityIndicatorRecordAction;
use App\Application\Quality\Actions\SaveQualityIndicatorRecordAction;
use App\Application\Quality\DTOs\QualityIndicatorRecordData;
use App\Domain\Quality\Repositories\QualityIndicatorRecordRepositoryInterface;
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

    /** @var string */
    public $status = 'draft';

    /** @var bool */
    public $isEdit = false;

    protected $listeners = ['input-record' => 'loadIndicator'];

    protected function rules(): array
    {
        return [
            'recordedDate'     => ['required', 'date'],
            'numeratorValue'   => ['required', 'integer', 'min:0'],
            'denominatorValue' => ['required', 'integer', 'min:0'],
            'notes'            => ['nullable', 'string'],
        ];
    }

    public function loadIndicator(int $indicatorId, ?string $date = null): void
    {
        $this->resetExcept([]);

        $indicator = QualityIndicator::findOrFail($indicatorId);
        $this->indicatorId = $indicator->id;
        $this->indicatorName = $indicator->title;

        if ($date) {
            $record = app(QualityIndicatorRecordRepositoryInterface::class)
                ->findByIndicatorAndDate($indicatorId, $date);

            if ($record) {
                $this->recordedDate = $record->recorded_date;
                $this->numeratorValue = $record->numerator_value;
                $this->denominatorValue = $record->denominator_value;
                $this->notes = $record->notes;
                $this->status = $record->status ?? 'draft';
                $this->isEdit = true;
            }
        } else {
            $this->recordedDate = now()->format('Y-m-d');
            $this->numeratorValue = 0;
            $this->denominatorValue = 0;
            $this->notes = '';
            $this->status = 'draft';
            $this->isEdit = false;
        }

        $this->dispatchBrowserEvent('open-modal', ['id' => 'modal-input-record-indikator']);
    }

    public function save(SaveQualityIndicatorRecordAction $action): void
    {
        if (in_array($this->status, ['submitted', 'approved'])) {
            $this->flashError('Data telah dikunci dan tidak dapat diubah.');

            return;
        }

        $this->validate();

        $data = QualityIndicatorRecordData::from([
            'indicator_id'      => $this->indicatorId,
            'recorded_date'     => $this->recordedDate,
            'numerator_value'   => $this->numeratorValue,
            'denominator_value' => $this->denominatorValue,
            'notes'             => $this->notes,
            'recorded_by'       => user()->nik,
            'status'            => 'draft', // saving as draft
        ]);

        $action->execute($data);

        $this->flashSuccess('Penilaian harian berhasil disimpan sebagai draft.');
        $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-input-record-indikator']);
        $this->emit('record-saved');
        $this->reset(['numeratorValue', 'denominatorValue', 'notes', 'isEdit', 'status']);
    }

    public function submit(SaveQualityIndicatorRecordAction $action): void
    {
        if (in_array($this->status, ['submitted', 'approved'])) {
            $this->flashError('Data telah dikunci.');

            return;
        }

        $this->validate();

        $data = QualityIndicatorRecordData::from([
            'indicator_id'      => $this->indicatorId,
            'recorded_date'     => $this->recordedDate,
            'numerator_value'   => $this->numeratorValue,
            'denominator_value' => $this->denominatorValue,
            'notes'             => $this->notes,
            'recorded_by'       => user()->nik,
            'status'            => 'submitted', // lock and submit
        ]);

        $action->execute($data);

        $this->flashSuccess('Penilaian harian berhasil dikunci dan diserahkan.');
        $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-input-record-indikator']);
        $this->emit('record-saved');
        $this->reset(['numeratorValue', 'denominatorValue', 'notes', 'isEdit', 'status']);
    }

    public function delete(DeleteQualityIndicatorRecordAction $action): void
    {
        if (in_array($this->status, ['submitted', 'approved'])) {
            $this->flashError('Data telah dikunci dan tidak dapat dihapus.');

            return;
        }

        $action->execute($this->indicatorId, $this->recordedDate);

        $this->flashSuccess('Data penilaian berhasil dihapus.');
        $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-input-record-indikator']);
        $this->emit('record-saved');
        $this->reset(['numeratorValue', 'denominatorValue', 'notes', 'isEdit', 'status']);
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.modal.input-record-indikator');
    }
}
