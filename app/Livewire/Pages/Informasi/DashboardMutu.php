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
            ->whereIn('status', ['submitted', 'approved', 'approved_with_correction'])
            ->whereBetween('recorded_date', [$this->tglAwal, $this->tglAkhir])
            ->when($this->depId, fn ($q) => $q->whereHas('indicator', fn ($q) => $q->where('dep_id', $this->depId)))
            ->count();
    }

    public function getAverageAchievementProperty(): float
    {
        $row = QualityIndicatorRecord::query()
            ->selectRaw('AVG((numerator_value / NULLIF(denominator_value, 0)) * 100) as avg_capaian')
            ->where('status', 'approved')
            ->whereBetween('recorded_date', [$this->tglAwal, $this->tglAkhir])
            ->when($this->depId, fn ($q) => $q->whereHas('indicator', fn ($q) => $q->where('dep_id', $this->depId)))
            ->when($this->kategoriId, fn ($q) => $q->whereHas('indicator.profile', fn ($q) => $q->where('quality_indicator_category_id', $this->kategoriId)))
            ->first();

        return round((float) ($row->avg_capaian ?? 0), 2);
    }

    public function getPendingValidationCountProperty(): int
    {
        return QualityIndicatorRecord::query()
            ->where('status', 'submitted')
            ->when($this->depId, fn ($q) => $q->whereHas('indicator', fn ($q) => $q->where('dep_id', $this->depId)))
            ->count();
    }

    public function getAchievementPerDepartemenProperty(): array
    {
        $aggByDepId = QualityIndicatorRecord::query()
            ->selectRaw('quality_indicators.dep_id, AVG((quality_indicator_records.numerator_value / NULLIF(quality_indicator_records.denominator_value, 0)) * 100) as avg_capaian')
            ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
            ->where('quality_indicator_records.status', 'approved')
            ->whereBetween('quality_indicator_records.recorded_date', [$this->tglAwal, $this->tglAkhir])
            ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
            ->when($this->kategoriId, fn ($q) => $q->whereHas('indicator.profile', fn ($q) => $q->where('quality_indicator_category_id', $this->kategoriId)))
            ->groupBy('quality_indicators.dep_id')
            ->get()
            ->keyBy('dep_id');

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
            ->whereBetween('recorded_date', [$this->tglAwal, $this->tglAkhir])
            ->when($this->depId, fn ($q) => $q->whereHas('indicator', fn ($q) => $q->where('dep_id', $this->depId)))
            ->when($this->kategoriId, fn ($q) => $q->whereHas('indicator.profile', fn ($q) => $q->where('quality_indicator_category_id', $this->kategoriId)))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $labels = [
            'draft'                    => 'Draft',
            'submitted'                => 'Submitted',
            'approved'                 => 'Approved',
            'rejected'                 => 'Rejected',
            'approved_with_correction' => 'Approved w/ Correction',
        ];

        $colors = [
            'draft'                    => '#6c757d',
            'submitted'                => '#17a2b8',
            'approved'                 => '#28a745',
            'rejected'                 => '#dc3545',
            'approved_with_correction' => '#007bff',
        ];

        $data = [];
        foreach ($labels as $key => $label) {
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

        $results = QualityIndicatorRecord::query()
            ->selectRaw("DATE_FORMAT(recorded_date, '%Y-%m') as bulan, AVG((numerator_value / NULLIF(denominator_value, 0)) * 100) as avg_capaian")
            ->where('status', 'approved')
            ->whereBetween('recorded_date', [$start, $end])
            ->when($this->depId, fn ($q) => $q->whereHas('indicator', fn ($q) => $q->where('dep_id', $this->depId)))
            ->when($this->kategoriId, fn ($q) => $q->whereHas('indicator.profile', fn ($q) => $q->where('quality_indicator_category_id', $this->kategoriId)))
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

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
        return QualityIndicatorRecord::query()
            ->selectRaw('quality_indicator_categories.name, AVG((quality_indicator_records.numerator_value / NULLIF(quality_indicator_records.denominator_value, 0)) * 100) as avg_capaian')
            ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
            ->join('quality_indicator_profiles', 'quality_indicators.quality_indicator_profile_id', '=', 'quality_indicator_profiles.id')
            ->join('quality_indicator_categories', 'quality_indicator_profiles.quality_indicator_category_id', '=', 'quality_indicator_categories.id')
            ->where('quality_indicator_records.status', 'approved')
            ->whereBetween('quality_indicator_records.recorded_date', [$this->tglAwal, $this->tglAkhir])
            ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
            ->when($this->kategoriId, fn ($q) => $q->where('quality_indicator_profiles.quality_indicator_category_id', $this->kategoriId))
            ->groupBy('quality_indicator_categories.name')
            ->orderBy('quality_indicator_categories.name')
            ->get()
            ->toArray();
    }

    public function getTopIndicatorsProperty(): array
    {
        return QualityIndicatorRecord::query()
            ->selectRaw(
                'quality_indicator_profiles.title,
                quality_indicator_records.indicator_id,
                AVG((quality_indicator_records.numerator_value / NULLIF(quality_indicator_records.denominator_value, 0)) * 100) as avg_capaian'
            )
            ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
            ->join('quality_indicator_profiles', 'quality_indicators.quality_indicator_profile_id', '=', 'quality_indicator_profiles.id')
            ->where('quality_indicator_records.status', 'approved')
            ->whereBetween('quality_indicator_records.recorded_date', [$this->tglAwal, $this->tglAkhir])
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
            ->selectRaw(
                'quality_indicator_profiles.title,
                quality_indicator_records.indicator_id,
                AVG((quality_indicator_records.numerator_value / NULLIF(quality_indicator_records.denominator_value, 0)) * 100) as avg_capaian'
            )
            ->join('quality_indicators', 'quality_indicator_records.indicator_id', '=', 'quality_indicators.id')
            ->join('quality_indicator_profiles', 'quality_indicators.quality_indicator_profile_id', '=', 'quality_indicator_profiles.id')
            ->where('quality_indicator_records.status', 'approved')
            ->whereBetween('quality_indicator_records.recorded_date', [$this->tglAwal, $this->tglAkhir])
            ->when($this->depId, fn ($q) => $q->where('quality_indicators.dep_id', $this->depId))
            ->when($this->kategoriId, fn ($q) => $q->where('quality_indicator_profiles.quality_indicator_category_id', $this->kategoriId))
            ->groupBy('quality_indicator_profiles.title', 'quality_indicator_records.indicator_id')
            ->orderBy('avg_capaian', 'asc')
            ->limit(5)
            ->get()
            ->toArray();
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
