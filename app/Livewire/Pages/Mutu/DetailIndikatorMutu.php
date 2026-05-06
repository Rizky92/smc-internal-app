<?php

namespace App\Livewire\Pages\Mutu;

use App\Application\Quality\Actions\DeleteQualityIndicatorRecordAction;
use App\Domain\Quality\Repositories\QualityIndicatorRecordRepositoryInterface;
use App\Domain\Quality\Repositories\QualityIndicatorRepositoryInterface;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Aplikasi\User;
use App\Models\Quality\QualityIndicator;
use App\View\Components\BaseLayout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class DetailIndikatorMutu extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;

    /** @var int */
    public $indicatorId;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected $listeners = [
        'record-saved' => '$refresh',
    ];

    public function mount(int $indicatorId): void
    {
        $this->indicatorId = $indicatorId;
        $this->defaultValues();
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            'Riwayat Penilaian' => fn () => $this->records->map(fn ($record) => [
                $record->recorded_date,
                $record->numerator_value,
                $record->denominator_value,
                $record->denominator_value > 0
                    ? round(($record->numerator_value / $record->denominator_value) * 100, 2).'%'
                    : '0%',
                $record->notes,
                $record->recorder->nm_user ?? '-',
            ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tanggal',
            'Numerator',
            'Denominator',
            'Capaian (%)',
            'Catatan',
            'Petugas',
        ];
    }

    protected function pageHeaders(): array
    {
        $indicator = $this->indicator;

        return [
            'LAPORAN PENILAIAN INDIKATOR MUTU',
            'INDIKATOR: '.strtoupper($indicator->title),
            'PERIODE: '.carbon($this->tglAwal)->format('d/m/Y').' s.d '.carbon($this->tglAkhir)->format('d/m/Y'),
            'UNIT: '.strtoupper($indicator->unit->nama ?? '-'),
            'STANDAR: '.$indicator->standard,
        ];
    }

    public function getIndicatorProperty(): ?QualityIndicator
    {
        return app(QualityIndicatorRepositoryInterface::class)->findById($this->indicatorId);
    }

    public function getRecordsProperty(): Collection
    {
        if ($this->isDeferred) {
            return collect();
        }

        return app(QualityIndicatorRecordRepositoryInterface::class)->getByIndicatorInRange(
            $this->indicatorId,
            $this->tglAwal,
            $this->tglAkhir
        );
    }

    /**
     * @return Collection|\Illuminate\Database\Eloquent\Collection
     *
     * @psalm-return Collection|\Illuminate\Database\Eloquent\Collection<User>
     */
    public function getRecordersProperty()
    {
        if ($this->isDeferred || $this->records->isEmpty()) {
            return collect();
        }

        $niks = $this->records->pluck('recorded_by')->filter()->unique();

        return User::query()
            ->whereIn(DB::raw('trim(pegawai.nik)'), $niks)
            ->get()
            ->keyBy('nik');
    }

    public function render(): View
    {
        $indicator = $this->indicator;
        $records = $this->records;

        if (! $this->isDeferred) {
            $this->dispatchBrowserEvent('update-chart', [
                'labels'   => $records->pluck('recorded_date')->map(fn ($d) => carbon($d)->format('d/m'))->toArray(),
                'data'     => $records->map(fn ($r) => $r->denominator_value > 0 ? round(($r->numerator_value / $r->denominator_value) * 100, 2) : 0)->toArray(),
                'standard' => (float) str_replace('%', '', $indicator->standard),
            ]);
        }

        return view('livewire.pages.mutu.detail-indikator-mutu', [
            'indicator' => $indicator,
            'records'   => $records,
        ])
            ->layout(BaseLayout::class, ['title' => 'Detail Indikator Mutu']);
    }

    public function delete(QualityIndicatorRepositoryInterface $repository): void
    {
        tracker_start('mysql_smc');

        $repository->delete($this->indicatorId);

        tracker_end('mysql_smc');

        $this->flashSuccess('Indikator Mutu berhasil dihapus.');

        $this->redirectRoute('admin.mutu.indikator-mutu');
    }

    public function deleteRecord(string $date, DeleteQualityIndicatorRecordAction $action): void
    {
        $action->execute($this->indicatorId, $date);

        $this->flashSuccess('Data penilaian berhasil dihapus.');
    }
}
