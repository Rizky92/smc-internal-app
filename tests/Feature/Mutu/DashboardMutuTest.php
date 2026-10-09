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
 * - Indikator-A (IT, Kategori-Satu, target ≥ 75): approved 80% & 60%, submitted, rejected, voided 0%; approved 20% di Februari; submitted di April
 * - Indikator-B (IT, Kategori-Dua, target ≥ 70): approved 50%, approved_with_correction 100%
 * - Indikator-C (ADM, Kategori-Satu, target ≥ 90): approved 100%, draft
 *
 * Capaian indikator = ΣN/ΣD × 100 record disetujui (Maret: A 70 tidak tercapai, B 75 tercapai,
 * C 100 tercapai). Agregat lintas indikator = % indikator tercapai dari yang bisa dinilai.
 */
class DashboardMutuTest extends MutuTestCase
{
    private const DEP_LAIN = 'ADM';

    private const BULAN = [
        'Apr 2025', 'May 2025', 'Jun 2025', 'Jul 2025', 'Aug 2025', 'Sep 2025',
        'Oct 2025', 'Nov 2025', 'Dec 2025', 'Jan 2026', 'Feb 2026', 'Mar 2026',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-03-15 08:00:00');

        $satu = QualityIndicatorCategoryFactory::new()->create(['name' => 'Kategori-Satu']);
        $dua = QualityIndicatorCategoryFactory::new()->create(['name' => 'Kategori-Dua']);

        $a = $this->indikator('Indikator-A', self::DEP_ID, $satu, 'gte', 75);
        $b = $this->indikator('Indikator-B', self::DEP_ID, $dua, 'gte', 70);
        $c = $this->indikator('Indikator-C', self::DEP_LAIN, $satu, 'gte', 90);

        $this->record($a, '2026-03-01', 'approved', 8);
        $this->record($a, '2026-03-02', 'approved', 6);
        $this->record($a, '2026-03-05', 'submitted', 1);
        $this->record($a, '2026-03-08', 'rejected', 1);
        $this->record($a, '2026-03-09', 'voided', 0);
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

    private function indikator(string $title, string $depId, QualityIndicatorCategory $category, ?string $operator = null, ?float $target = null): QualityIndicator
    {
        return QualityIndicatorFactory::new()->create([
            'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create([
                'title'                         => $title,
                'quality_indicator_category_id' => $category->id,
                'target_operator'               => $operator,
                'target_value'                  => $target,
            ]),
            'dep_id' => $depId,
        ]);
    }

    private function kategori(string $name): QualityIndicatorCategory
    {
        return QualityIndicatorCategory::query()->where('name', $name)->firstOrFail();
    }

    private function record(QualityIndicator $indicator, string $date, string $status, int $numerator, int $denominator = 10): void
    {
        QualityIndicatorRecordFactory::new()->create([
            'indicator_id'      => $indicator->id,
            'recorded_date'     => $date,
            'status'            => $status,
            'numerator_value'   => $numerator,
            'denominator_value' => $denominator,
        ]);
    }

    private function dashboard(string $tglAwal = '2026-03-01', string $tglAkhir = '2026-03-31'): TestableLivewire
    {
        return Livewire::actingAs($this->createUser())
            ->test(DashboardMutu::class)
            ->set('tglAwal', $tglAwal)
            ->set('tglAkhir', $tglAkhir)
            ->call('loadProperties');
    }

    /**
     * @param  array<int, float|null>  $data
     * @return array<int, float|null>
     */
    private function tren(array $data): array
    {
        return array_merge(array_fill(0, 12 - count($data), null), $data);
    }

    public function test_ringkasan_angka_untuk_semua_departemen(): void
    {
        $this->dashboard()
            ->assertViewHas('totalActiveIndicators', 3)
            ->assertViewHas('monthlyRecordsCount', 6)
            ->assertViewHas('ringkasanTercapai', ['persen' => 66.67, 'tercapai' => 2, 'dinilai' => 3, 'belum' => 0])
            ->assertViewHas('pendingValidationCount', 2)
            ->assertSee('2 dari 3 indikator tercapai');
    }

    public function test_ringkasan_angka_mengikuti_filter_departemen(): void
    {
        $this->dashboard()
            ->set('depId', self::DEP_ID)
            ->assertViewHas('monthlyRecordsCount', 5)
            ->assertViewHas('ringkasanTercapai', ['persen' => 50.0, 'tercapai' => 1, 'dinilai' => 2, 'belum' => 0])
            ->assertViewHas('pendingValidationCount', 2);

        $this->dashboard()
            ->set('depId', self::DEP_LAIN)
            ->assertViewHas('monthlyRecordsCount', 1)
            ->assertViewHas('ringkasanTercapai', ['persen' => 100.0, 'tercapai' => 1, 'dinilai' => 1, 'belum' => 0])
            ->assertViewHas('pendingValidationCount', 0);
    }

    public function test_chart_persen_tercapai_per_departemen_dan_per_kategori(): void
    {
        $this->dashboard()
            ->assertDispatchedBrowserEvent('update-chart-per-dept', [
                'labels'   => ['ADMISSION', 'Bagian IT/Programer/EDP'],
                'data'     => [100.0, 50.0],
                'tercapai' => [1, 1],
                'dinilai'  => [1, 2],
            ])
            ->assertDispatchedBrowserEvent('update-chart-per-kategori', [
                'labels'   => ['Kategori-Dua', 'Kategori-Satu'],
                'data'     => [100.0, 50.0],
                'tercapai' => [1, 1],
                'dinilai'  => [1, 2],
            ]);
    }

    public function test_chart_mengikuti_filter_departemen(): void
    {
        $this->dashboard()
            ->set('depId', self::DEP_LAIN)
            ->assertDispatchedBrowserEvent('update-chart-per-dept', [
                'labels'   => ['ADMISSION'],
                'data'     => [100.0],
                'tercapai' => [1],
                'dinilai'  => [1],
            ])
            ->assertDispatchedBrowserEvent('update-chart-per-kategori', [
                'labels'   => ['Kategori-Satu'],
                'data'     => [100.0],
                'tercapai' => [1],
                'dinilai'  => [1],
            ]);
    }

    public function test_angka_dan_chart_mengikuti_filter_kategori(): void
    {
        $this->dashboard()
            ->set('kategoriId', $this->kategori('Kategori-Satu')->id)
            ->assertViewHas('ringkasanTercapai', ['persen' => 50.0, 'tercapai' => 1, 'dinilai' => 2, 'belum' => 0])
            ->assertDispatchedBrowserEvent('update-chart-per-dept', [
                'labels'   => ['ADMISSION', 'Bagian IT/Programer/EDP'],
                'data'     => [100.0, 0.0],
                'tercapai' => [1, 0],
                'dinilai'  => [1, 1],
            ])
            ->assertDispatchedBrowserEvent('update-chart-per-kategori', [
                'labels'   => ['Kategori-Satu'],
                'data'     => [50.0],
                'tercapai' => [1],
                'dinilai'  => [2],
            ])
            ->assertDispatchedBrowserEvent('update-chart-status', [
                'labels' => ['Draft', 'Submitted', 'Approved', 'Rejected', 'Approved w/ Correction', 'Voided'],
                'data'   => [1, 1, 3, 1, 0, 1],
                'colors' => ['#6c757d', '#17a2b8', '#28a745', '#dc3545', '#007bff', '#343a40'],
            ])
            ->assertDispatchedBrowserEvent('update-chart-trend', [
                'labels'   => self::BULAN,
                'data'     => $this->tren([0.0, 50.0]),
                'tercapai' => $this->tren([0, 1]),
                'dinilai'  => $this->tren([1, 2]),
            ]);
    }

    public function test_chart_distribusi_status(): void
    {
        $this->dashboard()
            ->assertDispatchedBrowserEvent('update-chart-status', [
                'labels' => ['Draft', 'Submitted', 'Approved', 'Rejected', 'Approved w/ Correction', 'Voided'],
                'data'   => [1, 1, 4, 1, 1, 1],
                'colors' => ['#6c757d', '#17a2b8', '#28a745', '#dc3545', '#007bff', '#343a40'],
            ]);
    }

    public function test_tren_persen_tercapai_dua_belas_bulan_dengan_jumlah_untuk_tooltip(): void
    {
        // Februari hanya A (20%, target ≥ 75) yang bisa dinilai; bulan tanpa data = null, bukan 0.
        $this->dashboard()
            ->assertDispatchedBrowserEvent('update-chart-trend', [
                'labels'   => self::BULAN,
                'data'     => $this->tren([0.0, 66.67]),
                'tercapai' => $this->tren([0, 2]),
                'dinilai'  => $this->tren([1, 3]),
            ]);
    }

    public function test_indikator_capaian_tertinggi_dan_terendah_dengan_badge_status(): void
    {
        $this->dashboard()
            ->assertSeeInOrder(['Indikator-C', 'Indikator-B', 'Indikator-A', 'Indikator-A', 'Indikator-B', 'Indikator-C'])
            ->assertSeeInOrder(['Indikator-A', 'Tidak Tercapai']);
    }

    public function test_indikator_ge_dan_le_yang_sama_sama_tercapai_dihitung_100_persen(): void
    {
        $satu = $this->kategori('Kategori-Satu');

        // Rata-rata capaian (95 + 3) / 2 = 49% tidak bermakna; keduanya tercapai.
        $this->record($this->indikator('Kebersihan Tangan', self::DEP_ID, $satu, 'gte', 85), '2026-01-05', 'approved', 95, 100);
        $this->record($this->indikator('Penundaan Operasi', self::DEP_ID, $satu, 'lte', 5), '2026-01-05', 'approved', 3, 100);

        $this->dashboard('2026-01-01', '2026-01-31')
            ->assertViewHas('ringkasanTercapai', fn (array $r): bool => $r['persen'] === 100.0 && $r['tercapai'] === 2 && $r['dinilai'] === 2);
    }

    public function test_capaian_indikator_memakai_total_numerator_dibagi_total_denominator(): void
    {
        $d = $this->indikator('Indikator-D', self::DEP_ID, $this->kategori('Kategori-Satu'), 'gte', 85);

        // Rata-rata rasio harian = (50 + 90) / 2 = 70 (tidak tercapai); ΣN/ΣD = 91 / 102 × 100 = 89,22 (tercapai).
        $this->record($d, '2026-01-05', 'approved', 1, 2);
        $this->record($d, '2026-01-06', 'approved', 90, 100);

        $this->dashboard('2026-01-01', '2026-01-31')
            ->assertViewHas('ringkasanTercapai', fn (array $r): bool => $r['persen'] === 100.0 && $r['dinilai'] === 1)
            ->assertSee('89.22%');
    }

    public function test_indikator_tanpa_target_dan_denominator_nol_dikecualikan(): void
    {
        $satu = $this->kategori('Kategori-Satu');

        $this->record($this->indikator('Tanpa Target', self::DEP_ID, $satu), '2026-01-05', 'approved', 9, 10);
        $this->record($this->indikator('Denominator Nol', self::DEP_ID, $satu, 'gte', 80), '2026-01-05', 'approved', 0, 0);
        $this->record($this->indikator('Bisa Dinilai', self::DEP_ID, $satu, 'gte', 80), '2026-01-05', 'approved', 85, 100);

        // Aktif = A, B, C (tanpa data Januari) + 3 baru; hanya "Bisa Dinilai" yang bisa dinilai.
        $this->dashboard('2026-01-01', '2026-01-31')
            ->assertViewHas('ringkasanTercapai', ['persen' => 100.0, 'tercapai' => 1, 'dinilai' => 1, 'belum' => 5])
            ->assertSee('5 belum bisa dinilai');
    }

    public function test_grup_tanpa_indikator_yang_bisa_dinilai_bernilai_null(): void
    {
        $this->record($this->indikator('Tanpa Target ADM', self::DEP_LAIN, $this->kategori('Kategori-Dua')), '2026-01-05', 'approved', 9, 10);

        $this->dashboard('2026-01-01', '2026-01-31')
            ->assertViewHas('ringkasanTercapai', fn (array $r): bool => $r['persen'] === null && $r['dinilai'] === 0)
            ->assertDispatchedBrowserEvent('update-chart-per-dept', [
                'labels'   => ['ADMISSION'],
                'data'     => [null],
                'tercapai' => [0],
                'dinilai'  => [0],
            ])
            ->assertSee('Belum ada indikator yang bisa dinilai');
    }
}
