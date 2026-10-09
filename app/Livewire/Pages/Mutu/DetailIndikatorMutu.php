<?php

namespace App\Livewire\Pages\Mutu;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Models\Aplikasi\User;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorProfile;
use App\Models\Quality\QualityIndicatorRecord;
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
        'record-saved'    => '$refresh',
        'indicator-saved' => '$refresh',
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
                $this->recorders->get($record->recorded_by)->nama ?? '-',
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
            'INDIKATOR: '.strtoupper($indicator->profile->title ?? '-'),
            'PERIODE: '.carbon($this->tglAwal)->format('d/m/Y').' s.d '.carbon($this->tglAkhir)->format('d/m/Y'),
            'DEPARTEMEN: '.strtoupper($indicator->departemen->nama ?? '-'),
            'STANDAR: '.($indicator->profile->standard ?? '-'),
        ];
    }

    public function getIndicatorProperty(): ?QualityIndicator
    {
        return QualityIndicator::find($this->indicatorId);
    }

    public function getRecordsProperty(): Collection
    {
        if ($this->isDeferred) {
            return collect();
        }

        return QualityIndicatorRecord::query()
            ->withCount('histories')
            ->where('indicator_id', $this->indicatorId)
            ->periode($this->tglAwal, $this->tglAkhir)
            ->orderBy('recorded_date')
            ->get();
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

    /**
     * Run chart: capaian ΣN/ΣD per bulan dari record disetujui, 12 bulan yang berakhir di bulan `tglAkhir`.
     * Bulan tanpa data atau ΣD = 0 dikirim sebagai null (titik kosong, bukan 0%).
     *
     * @return array{labels: string[], data: array<int, float|null>, statuses: array<int, string|null>, target: float|null, targetLabel: string|null}
     */
    private function runChart(QualityIndicator $indicator): array
    {
        $profile = $indicator->profile ?? new QualityIndicatorProfile;
        $akhir = carbon($this->tglAkhir)->endOfMonth();
        $awal = $akhir->copy()->subMonthsNoOverflow(11)->startOfMonth();

        $capaian = QualityIndicatorRecord::query()
            ->where('indicator_id', $indicator->id)
            ->disetujui()
            ->periode($awal->toDateString(), $akhir->toDateString())
            ->selectRaw("DATE_FORMAT(recorded_date, '%Y-%m') as bulan")
            ->selectCapaian()
            ->groupBy('bulan')
            ->pluck('capaian', 'bulan');

        $chart = ['labels' => [], 'data' => [], 'statuses' => []];

        for ($bulan = $awal->copy(); $bulan->lte($akhir); $bulan->addMonthNoOverflow()) {
            $nilai = $capaian->get($bulan->format('Y-m'));
            $nilai = $nilai === null ? null : round((float) $nilai, 2);

            $chart['labels'][] = $bulan->format('M Y');
            $chart['data'][] = $nilai;
            $chart['statuses'][] = $nilai === null ? null : $profile->achievementStatus($nilai);
        }

        $targetLabel = $profile->targetLabel();

        return $chart + [
            'target'      => $targetLabel ? $profile->target_value : null,
            'targetLabel' => $targetLabel,
        ];
    }

    public function render(): View
    {
        $indicator = $this->indicator;
        $records = $this->records;

        if (! $this->isDeferred) {
            $this->dispatchBrowserEvent('update-chart', $this->runChart($indicator));
        }

        return view('livewire.pages.mutu.detail-indikator-mutu', [
            'indicator' => $indicator,
            'records'   => $records,
        ])
            ->layout(BaseLayout::class, ['title' => 'Detail Indikator Mutu']);
    }

    public function delete(): void
    {
        tracker_start('mysql_smc');

        QualityIndicator::destroy($this->indicatorId);

        tracker_end('mysql_smc');

        $this->flashSuccess('Mapping Indikator Unit berhasil dihapus.');

        $this->redirectRoute('admin.mutu.indikator-mutu');
    }

    public function deleteRecord(string $date): void
    {
        $record = QualityIndicatorRecord::tanggal($this->indicatorId, $date)->first();

        if (! $record) {
            $this->flashError('Data penilaian tidak ditemukan.');

            return;
        }

        if ($record->isLocked()) {
            $this->flashError('Data telah dikunci dan tidak dapat dihapus.');

            return;
        }

        if (! $record->canBeDeleted()) {
            $this->flashError('Data yang pernah diserahkan tidak dapat dihapus.');

            return;
        }

        tracker_start('mysql_smc');

        $record->delete();

        tracker_end('mysql_smc');

        $this->flashSuccess('Data penilaian berhasil dihapus.');
    }
}
