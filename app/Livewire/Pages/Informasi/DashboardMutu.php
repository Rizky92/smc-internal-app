<?php

namespace App\Livewire\Pages\Informasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Kepegawaian\Departemen;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorCategory;
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

    public function getAverageAchievementProperty(): float
    {
        $row = $this->rataRataCapaianIndikator(
            QualityIndicatorRecord::query()
                ->disetujui()
                ->periode($this->tglAwal, $this->tglAkhir)
                ->when($this->depId, fn ($q) => $q->departemen($this->depId))
                ->when($this->kategoriId, fn ($q) => $q->kategori($this->kategoriId))
        )->first();

        return round((float) ($row->avg_capaian ?? 0), 2);
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
        $aggByDepId = $this->rataRataCapaianIndikator(
            QualityIndicatorRecord::query()
                ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
                ->disetujui()
                ->periode($this->tglAwal, $this->tglAkhir)
                ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
                ->when($this->kategoriId, fn ($q) => $q->kategori($this->kategoriId)),
            ['dep_id' => 'quality_indicators.dep_id']
        )->keyBy('dep_id');

        $allDep = Departemen::query()->whereIn('dep_id', $aggByDepId->keys())->pluck('nama', 'dep_id');

        $result = [];
        foreach ($aggByDepId as $depId => $row) {
            $result[] = [
                'nama'        => $allDep[$depId] ?? $depId,
                'avg_capaian' => round((float) $row->avg_capaian, 2),
            ];
        }

        usort($result, fn ($a, $b) => strcmp($a['nama'], $b['nama']));

        return $result;
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

        $results = $this->rataRataCapaianIndikator(
            QualityIndicatorRecord::query()
                ->disetujui()
                ->periode($start, $end)
                ->when($this->depId, fn ($q) => $q->departemen($this->depId))
                ->when($this->kategoriId, fn ($q) => $q->kategori($this->kategoriId)),
            ['bulan' => "DATE_FORMAT(quality_indicator_records.recorded_date, '%Y-%m')"]
        )->keyBy('bulan');

        $data = [];
        $labels = [];
        for ($i = 11; $i >= 0; $i--) {
            $bulan = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $data[] = round((float) ($results[$bulan]->avg_capaian ?? 0), 2);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    public function getAchievementPerCategoryProperty(): array
    {
        return $this->rataRataCapaianIndikator(
            QualityIndicatorRecord::query()
                ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
                ->join('quality_indicator_profiles', 'quality_indicators.quality_indicator_profile_id', '=', 'quality_indicator_profiles.id')
                ->join('quality_indicator_categories', 'quality_indicator_profiles.quality_indicator_category_id', '=', 'quality_indicator_categories.id')
                ->disetujui()
                ->periode($this->tglAwal, $this->tglAkhir)
                ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
                ->when($this->kategoriId, fn ($q) => $q->where('quality_indicator_profiles.quality_indicator_category_id', $this->kategoriId)),
            ['name' => 'quality_indicator_categories.name']
        )
            ->sortBy('name')
            ->map(fn (object $row): array => ['name' => $row->name, 'avg_capaian' => $row->avg_capaian])
            ->values()
            ->all();
    }

    public function getTopIndicatorsProperty(): array
    {
        return QualityIndicatorRecord::query()
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
            ->toArray();
    }

    public function getBottomIndicatorsProperty(): array
    {
        return QualityIndicatorRecord::query()
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
            ->toArray();
    }

    /**
     * Agregat lintas indikator: capaian tiap indikator (ΣN/ΣD) dirata-rata dengan bobot sama, supaya
     * indikator ber-denominator besar tidak mendominasi. Indikator dengan total denominator 0 tidak
     * ikut dirata-rata. Langkah sementara sampai agregat diganti menjadi "% indikator tercapai".
     *
     * @param  array<string, string>  $groups  alias => ekspresi SQL pengelompokan
     * @return Collection<int, object>
     */
    private function rataRataCapaianIndikator(Builder $records, array $groups = []): Collection
    {
        $perIndikator = $records
            ->select('quality_indicator_records.indicator_id')
            ->selectCapaian()
            ->groupBy('quality_indicator_records.indicator_id');

        foreach ($groups as $alias => $expression) {
            $perIndikator->selectRaw("{$expression} as {$alias}")->groupBy(DB::raw($expression));
        }

        return $perIndikator->getQuery()->newQuery()
            ->fromSub($perIndikator, 'per_indikator')
            ->select(array_keys($groups))
            ->selectRaw('AVG(capaian) as avg_capaian')
            ->when($groups, fn ($q) => $q->groupBy(array_keys($groups)))
            ->get();
    }

    public function render(): View
    {
        if (! $this->isDeferred) {
            $perDept = $this->achievementPerDepartemen;
            $statusDist = $this->statusDistribution;
            $monthlyTrend = $this->monthlyTrend;
            $perKategori = $this->achievementPerCategory;

            $this->dispatchBrowserEvent('update-chart-per-dept', [
                'labels' => array_column($perDept, 'nama'),
                'data'   => array_map(fn ($v) => round((float) $v, 2), array_column($perDept, 'avg_capaian')),
            ]);

            $this->dispatchBrowserEvent('update-chart-status', [
                'labels' => array_column($statusDist, 'label'),
                'data'   => array_map('intval', array_column($statusDist, 'value')),
                'colors' => array_column($statusDist, 'color'),
            ]);

            $this->dispatchBrowserEvent('update-chart-trend', [
                'labels' => $monthlyTrend['labels'],
                'data'   => $monthlyTrend['data'],
            ]);

            $this->dispatchBrowserEvent('update-chart-per-kategori', [
                'labels' => array_column($perKategori, 'name'),
                'data'   => array_map(fn ($v) => round((float) $v, 2), array_column($perKategori, 'avg_capaian')),
            ]);
        }

        return view('livewire.pages.informasi.dashboard-mutu', [
            'totalActiveIndicators'  => $this->totalActiveIndicators,
            'monthlyRecordsCount'    => $this->monthlyRecordsCount,
            'averageAchievement'     => $this->averageAchievement,
            'pendingValidationCount' => $this->pendingValidationCount,
        ])
            ->layout(BaseLayout::class, ['title' => 'Dashboard Mutu']);
    }
}
