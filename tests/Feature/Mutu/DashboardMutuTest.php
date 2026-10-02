<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Informasi\DashboardMutu;
use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorCategory;
use Database\Factories\Quality\QualityIndicatorCategoryFactory;
use Database\Factories\Quality\QualityIndicatorFactory;
use Database\Factories\Quality\QualityIndicatorProfileFactory;
use Database\Factories\Quality\QualityIndicatorRecordFactory;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;

/**
 * Fixture Maret 2026:
 * - Indikator-A (IT, Kategori-Satu): approved 80% & 60%, submitted, rejected; approved 20% di Februari; submitted di April
 * - Indikator-B (IT, Kategori-Dua): approved 50%, approved_with_correction 100%
 * - Indikator-C (ADM, Kategori-Satu): approved 100%, draft
 */
class DashboardMutuTest extends MutuTestCase
{
    private const DEP_LAIN = 'ADM';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-03-15 08:00:00');

        $satu = QualityIndicatorCategoryFactory::new()->create(['name' => 'Kategori-Satu']);
        $dua = QualityIndicatorCategoryFactory::new()->create(['name' => 'Kategori-Dua']);

        $a = $this->indikator('Indikator-A', self::DEP_ID, $satu);
        $b = $this->indikator('Indikator-B', self::DEP_ID, $dua);
        $c = $this->indikator('Indikator-C', self::DEP_LAIN, $satu);

        $this->record($a, '2026-03-01', 'approved', 8);
        $this->record($a, '2026-03-02', 'approved', 6);
        $this->record($a, '2026-03-05', 'submitted', 1);
        $this->record($a, '2026-03-08', 'rejected', 1);
        $this->record($a, '2026-02-15', 'approved', 2);
        $this->record($a, '2026-04-01', 'submitted', 1);
        $this->record($b, '2026-03-03', 'approved', 5);
        $this->record($b, '2026-03-06', 'approved_with_correction', 10);
        $this->record($c, '2026-03-04', 'approved', 10);
        $this->record($c, '2026-03-07', 'draft', 1);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function indikator(string $title, string $depId, QualityIndicatorCategory $category): QualityIndicator
    {
        return QualityIndicatorFactory::new()->create([
            'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create([
                'title'                         => $title,
                'quality_indicator_category_id' => $category->id,
            ]),
            'dep_id' => $depId,
        ]);
    }

    private function record(QualityIndicator $indicator, string $date, string $status, int $numerator): void
    {
        QualityIndicatorRecordFactory::new()->create([
            'indicator_id'      => $indicator->id,
            'recorded_date'     => $date,
            'status'            => $status,
            'numerator_value'   => $numerator,
            'denominator_value' => 10,
        ]);
    }

    private function dashboard(): TestableLivewire
    {
        return Livewire::actingAs($this->createUser())
            ->test(DashboardMutu::class)
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties');
    }

    public function test_ringkasan_angka_untuk_semua_departemen(): void
    {
        $this->dashboard()
            ->assertViewHas('totalActiveIndicators', 3)
            ->assertViewHas('monthlyRecordsCount', 6)
            ->assertViewHas('averageAchievement', 78.0)
            ->assertViewHas('pendingValidationCount', 2);
    }

    public function test_ringkasan_angka_mengikuti_filter_departemen(): void
    {
        $this->dashboard()
            ->set('depId', self::DEP_ID)
            ->assertViewHas('monthlyRecordsCount', 5)
            ->assertViewHas('averageAchievement', 72.5)
            ->assertViewHas('pendingValidationCount', 2);

        $this->dashboard()
            ->set('depId', self::DEP_LAIN)
            ->assertViewHas('monthlyRecordsCount', 1)
            ->assertViewHas('averageAchievement', 100.0)
            ->assertViewHas('pendingValidationCount', 0);
    }

    public function test_chart_capaian_per_departemen_dan_per_kategori(): void
    {
        $this->dashboard()
            ->assertDispatchedBrowserEvent('update-chart-per-dept', [
                'labels' => ['ADMISSION', 'Bagian IT/Programer/EDP'],
                'data'   => [100.0, 72.5],
            ])
            ->assertDispatchedBrowserEvent('update-chart-per-kategori', [
                'labels' => ['Kategori-Dua', 'Kategori-Satu'],
                'data'   => [75.0, 80.0],
            ]);
    }

    public function test_chart_capaian_mengikuti_filter_departemen(): void
    {
        $this->dashboard()
            ->set('depId', self::DEP_LAIN)
            ->assertDispatchedBrowserEvent('update-chart-per-dept', [
                'labels' => ['ADMISSION'],
                'data'   => [100.0],
            ])
            ->assertDispatchedBrowserEvent('update-chart-per-kategori', [
                'labels' => ['Kategori-Satu'],
                'data'   => [100.0],
            ]);
    }

    public function test_chart_distribusi_status(): void
    {
        $this->dashboard()
            ->assertDispatchedBrowserEvent('update-chart-status', [
                'labels' => ['Draft', 'Submitted', 'Approved', 'Rejected', 'Approved w/ Correction'],
                'data'   => [1, 1, 4, 1, 1],
                'colors' => ['#6c757d', '#17a2b8', '#28a745', '#dc3545', '#007bff'],
            ]);
    }

    public function test_chart_tren_dua_belas_bulan_terakhir(): void
    {
        $this->dashboard()
            ->assertDispatchedBrowserEvent('update-chart-trend', [
                'labels' => [
                    'Apr 2025', 'May 2025', 'Jun 2025', 'Jul 2025', 'Aug 2025', 'Sep 2025',
                    'Oct 2025', 'Nov 2025', 'Dec 2025', 'Jan 2026', 'Feb 2026', 'Mar 2026',
                ],
                'data' => [0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 20.0, 78.0],
            ]);
    }

    public function test_indikator_capaian_tertinggi_dan_terendah(): void
    {
        $this->dashboard()
            ->assertSeeInOrder(['Indikator-C', 'Indikator-B', 'Indikator-A', 'Indikator-A', 'Indikator-B', 'Indikator-C']);
    }
}
