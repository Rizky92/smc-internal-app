<?php

namespace App\Livewire\Pages\Mutu\Modal;

use App\Livewire\Concerns\FlashComponent;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorRecord;
use App\Models\Quality\QualityIndicatorRecordHistory;
use Illuminate\Support\Facades\DB;
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
    public $status = QualityIndicatorRecord::STATUS_DRAFT;

    /** @var bool */
    public $isEdit = false;

    /** @var string|null */
    public $alasanPenolakan;

    /**
     * Hanya untuk tampilan tombol hapus; aksi hapus tetap memeriksa database.
     *
     * @var bool
     */
    public $bisaDihapus = false;

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
            $record = $this->findRecord($indicatorId, $date);

            if ($record) {
                $this->recordedDate = $record->recorded_date;
                $this->numeratorValue = $record->numerator_value;
                $this->denominatorValue = $record->denominator_value;
                $this->notes = $record->notes;
                $this->status = $record->status ?? QualityIndicatorRecord::STATUS_DRAFT;
                $this->isEdit = true;
                $this->bisaDihapus = $record->canBeDeleted();
                $this->alasanPenolakan = $record->status === QualityIndicatorRecord::STATUS_REJECTED
                    ? $record->lastRejectionReason()
                    : null;
            }
        } else {
            $this->recordedDate = now()->format('Y-m-d');
            $this->numeratorValue = 0;
            $this->denominatorValue = 0;
            $this->notes = '';
            $this->status = QualityIndicatorRecord::STATUS_DRAFT;
            $this->isEdit = false;
        }

        $this->dispatchBrowserEvent('open-modal', ['id' => 'modal-input-record-indikator']);
    }

    public function save(): void
    {
        if ($this->isLocked()) {
            $this->flashError('Data telah dikunci dan tidak dapat diubah.');

            return;
        }

        $this->validate();

        $this->persist(QualityIndicatorRecord::STATUS_DRAFT);

        $this->flashSuccess('Penilaian harian berhasil disimpan sebagai draft.');
        $this->closeModalAndRefresh();
    }

    public function submit(): void
    {
        if ($this->isLocked()) {
            $this->flashError('Data telah dikunci.');

            return;
        }

        $this->validate();

        $this->persist(QualityIndicatorRecord::STATUS_SUBMITTED);

        $this->flashSuccess('Penilaian harian berhasil dikunci dan diserahkan.');
        $this->closeModalAndRefresh();
    }

    public function delete(): void
    {
        if ($this->isLocked()) {
            $this->flashError('Data telah dikunci dan tidak dapat dihapus.');

            return;
        }

        $record = $this->findRecord($this->indicatorId, $this->recordedDate);

        if ($record && ! $record->canBeDeleted()) {
            $this->flashError('Data yang pernah diserahkan tidak dapat dihapus.');

            return;
        }

        tracker_start('mysql_smc');

        optional($record)->delete();

        tracker_end('mysql_smc');

        $this->flashSuccess('Data penilaian berhasil dihapus.');
        $this->closeModalAndRefresh();
    }

    public function render(): View
    {
        // Hanya untuk tampilan; aksi tetap memeriksa status di database lewat isLocked().
        return view('livewire.pages.mutu.modal.input-record-indikator', [
            'isDisabled' => in_array($this->status, QualityIndicatorRecord::STATUSES_TERKUNCI, true),
        ]);
    }

    /**
     * Status kunci dibaca dari database karena properti `status` bisa diubah dari sisi klien.
     */
    protected function isLocked(): bool
    {
        if (empty($this->indicatorId) || empty($this->recordedDate)) {
            return false;
        }

        $record = $this->findRecord($this->indicatorId, $this->recordedDate);

        return $record !== null && $record->isLocked();
    }

    protected function findRecord(int $indicatorId, string $date): ?QualityIndicatorRecord
    {
        return QualityIndicatorRecord::tanggal($indicatorId, $date)->first();
    }

    protected function persist(string $status): void
    {
        tracker_start('mysql_smc');

        DB::connection('mysql_smc')->transaction(function () use ($status): void {
            $recordLama = $this->findRecord($this->indicatorId, $this->recordedDate);
            $statusLama = $recordLama ? $recordLama->status : null;

            $record = QualityIndicatorRecord::updateOrCreate(
                [
                    'indicator_id'  => $this->indicatorId,
                    'recorded_date' => $this->recordedDate,
                ],
                [
                    'numerator_value'   => $this->numeratorValue,
                    'denominator_value' => $this->denominatorValue,
                    'notes'             => $this->notes,
                    'recorded_by'       => user()->nik,
                    'status'            => $status,
                ]
            );

            // Simpan draft bukan transisi validasi, jadi hanya penyerahan yang dicatat.
            if ($status === QualityIndicatorRecord::STATUS_SUBMITTED) {
                $record->recordHistory(QualityIndicatorRecordHistory::ACTION_SUBMITTED, $statusLama);
            }
        });

        tracker_end('mysql_smc');
    }

    protected function closeModalAndRefresh(): void
    {
        $this->dispatchBrowserEvent('close-modal', ['id' => 'modal-input-record-indikator']);
        $this->emit('record-saved');
        $this->reset(['numeratorValue', 'denominatorValue', 'notes', 'isEdit', 'status', 'alasanPenolakan', 'bisaDihapus']);
    }
}
