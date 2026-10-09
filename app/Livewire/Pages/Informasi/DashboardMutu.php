<?php

namespace App\Livewire\Pages\Informasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Kepegawaian\Departemen;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorCategory;
use App\Models\Quality\QualityIndicatorProfile;
use App\Models\Quality\QualityIndicatorRecord;
use App\View\Components\BaseLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class DashboardMutu extends Component
{
    use DeferredLoading;
    use Filterable;
    use FlashComponent;
    use MenuTracker;

    public $tglAwal;

    public $tglAkhir;

    public $depId;

    public $kategoriId;

    public function mount(): void
    {
        $this->defaultValues();
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->depId = '';
        $this->kategoriId = '';
    }

    public function getDepartemenProperty(): array
    {
        return Departemen::query()->pluck('nama', 'dep_id')->all();
    }

    public function getKategoriProperty(): array
    {
        return QualityIndicatorCategory::query()->pluck('name', 'id')->all();
    }

    public function getTotalActiveIndicatorsProperty(): int
    {
        return QualityIndicator::query()->where('status', 'active')->count();
    }

    public function getMonthlyRecordsCountProperty(): int
    {
        return QualityIndicatorRecord::query()
            ->dilaporkan()
            ->periode($this->tglAwal, $this->tglAkhir)
            ->when($this->depId, fn ($q) => $q->departemen($this->depId))
            ->count();
    }

    /**
     * % indikator tercapai di periode filter, dengan jumlahnya. `belum` = indikator aktif dalam filter yang
     * belum bisa dinilai (target belum terstruktur, ΣD = 0, atau tanpa record disetujui).
     *
     * @return array{persen: float|null, tercapai: int, dinilai: int, belum: int}
     */
    public function getRingkasanTercapaiProperty(): array
    {
        $hasil = $this->persenTercapai(
            QualityIndicatorRecord::query()
                ->disetujui()
                ->periode($this->tglAwal, $this->tglAkhir)
                ->when($this->depId, fn ($q) => $q->departemen($this->depId))
                ->when($this->kategoriId, fn ($q) => $q->kategori($this->kategoriId))
        )->get('', ['tercapai' => 0, 'dinilai' => 0, 'persen' => null]);

        $aktif = QualityIndicator::query()
            ->where('status', 'active')
            ->when($this->depId, fn ($q) => $q->departemen($this->depId))
            ->when($this->kategoriId, fn ($q) => $q->whereHas('profile', fn ($q) => $q->where('quality_indicator_category_id', $this->kategoriId)))
            ->count();

        return [
            'persen'   => $hasil['persen'],
            'tercapai' => $hasil['tercapai'],
            'dinilai'  => $hasil['dinilai'],
            'belum'    => max(0, $aktif - $hasil['dinilai']),
        ];
    }

    public function getPendingValidationCountProperty(): int
    {
        return QualityIndicatorRecord::query()
            ->where('status', QualityIndicatorRecord::STATUS_SUBMITTED)
            ->when($this->depId, fn ($q) => $q->departemen($this->depId))
            ->count();
    }

    public function getAchievementPerDepartemenProperty(): array
    {
        $perDepId = $this->persenTercapai(
            QualityIndicatorRecord::query()
                ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
                ->disetujui()
                ->periode($this->tglAwal, $this->tglAkhir)
                ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
                ->when($this->kategoriId, fn ($q) => $q->kategori($this->kategoriId)),
            'quality_indicators.dep_id'
        );

        $allDep = Departemen::query()->whereIn('dep_id', $perDepId->keys())->pluck('nama', 'dep_id');

        return $perDepId
            ->map(fn (array $hasil, string $depId): array => ['nama' => $allDep[$depId] ?? $depId] + $hasil)
            ->sortBy('nama')
            ->values()
            ->all();
    }

    public function getStatusDistributionProperty(): array
    {
        $results = QualityIndicatorRecord::query()
            ->selectRaw('status, COUNT(*) as total')
            ->periode($this->tglAwal, $this->tglAkhir)
            ->when($this->depId, fn ($q) => $q->departemen($this->depId))
            ->when($this->kategoriId, fn ($q) => $q->kategori($this->kategoriId))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $colors = [
            QualityIndicatorRecord::STATUS_DRAFT                    => '#6c757d',
            QualityIndicatorRecord::STATUS_SUBMITTED                => '#17a2b8',
            QualityIndicatorRecord::STATUS_APPROVED                 => '#28a745',
            QualityIndicatorRecord::STATUS_REJECTED                 => '#dc3545',
            QualityIndicatorRecord::STATUS_APPROVED_WITH_CORRECTION => '#007bff',
            QualityIndicatorRecord::STATUS_VOIDED                   => '#343a40',
        ];

        $data = [];
        foreach (QualityIndicatorRecord::STATUS_LABELS as $key => $label) {
            $data[] = [
                'label' => $label,
                'value' => (int) ($results[$key]->total ?? 0),
                'color' => $colors[$key],
            ];
        }

        return $data;
    }

    public function getMonthlyTrendProperty(): array
    {
        $start = now()->subMonths(11)->startOfMonth()->format('Y-m-d');
        $end = now()->endOfMonth()->format('Y-m-d');

        $perBulan = $this->persenTercapai(
            QualityIndicatorRecord::query()
                ->disetujui()
                ->periode($start, $end)
                ->when($this->depId, fn ($q) => $q->departemen($this->depId))
                ->when($this->kategoriId, fn ($q) => $q->kategori($this->kategoriId)),
            "DATE_FORMAT(quality_indicator_records.recorded_date, '%Y-%m')"
        );

        $tren = ['labels' => [], 'data' => [], 'tercapai' => [], 'dinilai' => []];

        // Bulan tanpa indikator yang bisa dinilai = null (celah di grafik), bukan 0%.
        for ($i = 11; $i >= 0; $i--) {
            $hasil = $perBulan->get(now()->subMonths($i)->format('Y-m'));

            $tren['labels'][] = now()->subMonths($i)->format('M Y');
            $tren['data'][] = $hasil['persen'] ?? null;
            $tren['tercapai'][] = $hasil['tercapai'] ?? null;
            $tren['dinilai'][] = $hasil['dinilai'] ?? null;
        }

        return $tren;
    }

    public function getAchievementPerCategoryProperty(): array
    {
        return $this->persenTercapai(
            QualityIndicatorRecord::query()
                ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
                ->join('quality_indicator_profiles', 'quality_indicators.quality_indicator_profile_id', '=', 'quality_indicator_profiles.id')
                ->join('quality_indicator_categories', 'quality_indicator_profiles.quality_indicator_category_id', '=', 'quality_indicator_categories.id')
                ->disetujui()
                ->periode($this->tglAwal, $this->tglAkhir)
                ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
                ->when($this->kategoriId, fn ($q) => $q->where('quality_indicator_profiles.quality_indicator_category_id', $this->kategoriId)),
            'quality_indicator_categories.name'
        )
            ->map(fn (array $hasil, string $name): array => ['name' => $name] + $hasil)
            ->sortBy('name')
            ->values()
            ->all();
    }

    public function getTopIndicatorsProperty(): array
    {
        return $this->denganStatus(QualityIndicatorRecord::query()
            ->select('quality_indicator_profiles.title', 'quality_indicator_records.indicator_id')
            ->selectCapaian('avg_capaian')
            ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
            ->join('quality_indicator_profiles', 'quality_indicators.quality_indicator_profile_id', '=', 'quality_indicator_profiles.id')
            ->disetujui()
            ->periode($this->tglAwal, $this->tglAkhir)
            ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
            ->when($this->kategoriId, fn ($q) => $q->where('quality_indicator_profiles.quality_indicator_category_id', $this->kategoriId))
            ->groupBy('quality_indicator_profiles.title', 'quality_indicator_records.indicator_id')
            ->orderBy('avg_capaian', 'desc')
            ->limit(5)
            ->get()
            ->toArray());
    }

    public function getBottomIndicatorsProperty(): array
    {
        return $this->denganStatus(QualityIndicatorRecord::query()
            ->select('quality_indicator_profiles.title', 'quality_indicator_records.indicator_id')
            ->selectCapaian('avg_capaian')
            ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
            ->join('quality_indicator_profiles', 'quality_indicators.quality_indicator_profile_id', '=', 'quality_indicator_profiles.id')
            ->disetujui()
            ->periode($this->tglAwal, $this->tglAkhir)
            ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
            ->when($this->kategoriId, fn ($q) => $q->where('quality_indicator_profiles.quality_indicator_category_id', $this->kategoriId))
            ->groupBy('quality_indicator_profiles.title', 'quality_indicator_records.indicator_id')
            ->orderBy('avg_capaian', 'asc')
            ->limit(5)
            ->get()
            ->toArray());
    }

    /**
     * Agregat lintas indikator = % indikator tercapai dari yang bisa dinilai. Rata-rata capaian tidak dipakai
     * karena tidak bermakna untuk indikator dengan arah target berbeda (95% ≥ dan 3% ≤ sama-sama tercapai).
     * Status tiap indikator: capaian ΣN/ΣD (`selectCapaian()`) dibandingkan target lewat `achievementStatus()`;
     * indikator yang belum bisa dinilai tidak masuk pembilang maupun penyebut.
     *
     * @return Collection<string, array{persen: float|null, tercapai: int, dinilai: int}> dikunci nilai grup ('' tanpa grup)
     */
    private function persenTercapai(Builder $records, ?string $groupExpression = null): Collection
    {
        $perIndikator = $records
            ->select('quality_indicator_records.indicator_id')
            ->selectCapaian()
            ->groupBy('quality_indicator_records.indicator_id')
            ->when($groupExpression, fn ($q) => $q->selectRaw("{$groupExpression} as grup")->groupBy(DB::raw($groupExpression)))
            ->toBase()
            ->get();

        $status = $this->statusIndikator($perIndikator);

        return $perIndikator
            ->groupBy(fn (object $row): string => (string) ($row->grup ?? ''))
            ->map(function (Collection $rows) use ($status): array {
                $statuses = $rows->map(fn (object $row): string => $status($row->indicator_id, $row->capaian));
                $tercapai = $statuses->filter(fn (string $s): bool => $s === QualityIndicatorProfile::STATUS_TERCAPAI)->count();
                $dinilai = $statuses->reject(fn (string $s): bool => $s === QualityIndicatorProfile::STATUS_BELUM_DINILAI)->count();

                return [
                    'persen'   => $dinilai > 0 ? round($tercapai / $dinilai * 100, 2) : null,
                    'tercapai' => $tercapai,
                    'dinilai'  => $dinilai,
                ];
            });
    }

    /**
     * Fungsi (indicator_id, capaian) => status capaian, memakai target profil masing-masing indikator.
     *
     * @param  Collection<int, object>  $rows  baris dengan `indicator_id`
     */
    private function statusIndikator(Collection $rows): \Closure
    {
        $profiles = QualityIndicator::query()
            ->with('profile')
            ->whereIn('id', $rows->pluck('indicator_id')->unique()->values()->all())
            ->get()
            ->mapWithKeys(fn (QualityIndicator $indicator): array => [$indicator->id => $indicator->profile ?? new QualityIndicatorProfile]);

        return fn ($indicatorId, $capaian): string => ($profiles[$indicatorId] ?? new QualityIndicatorProfile)
            ->achievementStatus($capaian === null ? null : (float) $capaian);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items  baris Top/Bottom 5 dengan `indicator_id` dan `avg_capaian`
     * @return array<int, array<string, mixed>>
     */
    private function denganStatus(array $items): array
    {
        $status = $this->statusIndikator(collect($items)->map(fn (array $item): object => (object) $item));

        return array_map(fn (array $item): array => $item + ['status' => $status($item['indicator_id'], $item['avg_capaian'])], $items);
    }

    public function render(): View
    {
        if (! $this->isDeferred) {
            $perDept = $this->achievementPerDepartemen;
            $statusDist = $this->statusDistribution;
            $monthlyTrend = $this->monthlyTrend;
            $perKategori = $this->achievementPerCategory;

            $this->dispatchBrowserEvent('update-chart-per-dept', [
                'labels'   => array_column($perDept, 'nama'),
                'data'     => array_column($perDept, 'persen'),
                'tercapai' => array_column($perDept, 'tercapai'),
                'dinilai'  => array_column($perDept, 'dinilai'),
            ]);

            $this->dispatchBrowserEvent('update-chart-status', [
                'labels' => array_column($statusDist, 'label'),
                'data'   => array_map('intval', array_column($statusDist, 'value')),
                'colors' => array_column($statusDist, 'color'),
            ]);

            $this->dispatchBrowserEvent('update-chart-trend', $monthlyTrend);

            $this->dispatchBrowserEvent('update-chart-per-kategori', [
                'labels'   => array_column($perKategori, 'name'),
                'data'     => array_column($perKategori, 'persen'),
                'tercapai' => array_column($perKategori, 'tercapai'),
                'dinilai'  => array_column($perKategori, 'dinilai'),
            ]);
        }

        return view('livewire.pages.informasi.dashboard-mutu', [
            'totalActiveIndicators'  => $this->totalActiveIndicators,
            'monthlyRecordsCount'    => $this->monthlyRecordsCount,
            'ringkasanTercapai'      => $this->ringkasanTercapai,
            'pendingValidationCount' => $this->pendingValidationCount,
        ])
            ->layout(BaseLayout::class, ['title' => 'Dashboard Mutu']);
    }
}
