<?php

namespace Tests\Feature\Mutu;

use App\Livewire\Pages\Mutu\DetailIndikatorMutu;
use App\Livewire\Pages\Mutu\IndikatorMutu;
use App\Livewire\Pages\Mutu\Modal\InputIndikatorMutu;
use App\Models\Quality\QualityIndicator;
use Database\Factories\Quality\QualityIndicatorFactory;
use Database\Factories\Quality\QualityIndicatorProfileFactory;
use Database\Factories\Quality\QualityIndicatorRecordFactory;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Vtiful\Kernel\Excel;

class IndikatorMutuTest extends MutuTestCase
{
    private const DEP_LAIN = 'ADM';

    private const PESAN_TANPA_MAPPING = 'Belum terdapat mapping departemen pada jabatan Anda';

    private function indikator(string $title, string $depId = self::DEP_ID): QualityIndicator
    {
        return QualityIndicatorFactory::new()->create([
            'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create(['title' => $title]),
            'dep_id'                       => $depId,
        ]);
    }

    public function test_admin_bisa_memetakan_profil_ke_departemen(): void
    {
        $profile = QualityIndicatorProfileFactory::new()->create();

        Livewire::actingAs($this->createUser())
            ->test(InputIndikatorMutu::class)
            ->call('loadIndicator')
            ->set('quality_indicator_profile_id', $profile->id)
            ->set('dep_id', self::DEP_LAIN)
            ->set('person_in_charge', 'Kepala Unit')
            ->set('data_source', 'Rekam medis')
            ->set('status', 'active')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('indicator-saved');

        $this->assertDatabaseHas('quality_indicators', [
            'quality_indicator_profile_id' => $profile->id,
            'dep_id'                       => self::DEP_LAIN,
            'person_in_charge'             => 'Kepala Unit',
            'data_source'                  => 'Rekam medis',
            'status'                       => 'active',
        ], 'mysql_smc');
    }

    public function test_admin_bisa_mengubah_mapping_indikator(): void
    {
        $indicator = $this->indikator('Indikator Lama');

        Livewire::actingAs($this->createUser())
            ->test(InputIndikatorMutu::class)
            ->call('loadIndicator', $indicator->id)
            ->assertSet('quality_indicator_profile_id', $indicator->quality_indicator_profile_id)
            ->assertSet('dep_id', self::DEP_ID)
            ->assertSet('person_in_charge', $indicator->person_in_charge)
            ->set('person_in_charge', 'PJ Baru')
            ->set('status', 'inactive')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('indicator-saved');

        $indicator->refresh();

        $this->assertSame('PJ Baru', $indicator->person_in_charge);
        $this->assertSame('inactive', $indicator->status);
        $this->assertSame(1, QualityIndicator::where('quality_indicator_profile_id', $indicator->quality_indicator_profile_id)->count());
    }

    public function test_mapping_indikator_wajib_memiliki_profil_dan_departemen(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(InputIndikatorMutu::class)
            ->call('loadIndicator')
            ->call('save')
            ->assertHasErrors([
                'quality_indicator_profile_id' => 'required',
                'dep_id'                       => 'required',
            ])
            ->assertNotEmitted('indicator-saved');
    }

    public function test_tanpa_filter_user_melihat_indikator_departemennya_sendiri(): void
    {
        $this->indikator('Indikator Departemen Sendiri');
        $this->indikator('Indikator Departemen Lain', self::DEP_LAIN);

        Livewire::actingAs($this->createUser())
            ->test(IndikatorMutu::class)
            ->call('loadProperties')
            ->assertSee('Indikator Departemen Sendiri')
            ->assertDontSee('Indikator Departemen Lain');
    }

    public function test_user_tanpa_departemen_diberi_tahu_belum_ada_mapping(): void
    {
        Livewire::actingAs($this->createUser('MUTU-TANPA-DEP', ['mutu.*'], ''))
            ->test(IndikatorMutu::class)
            ->call('loadProperties')
            ->assertSee(self::PESAN_TANPA_MAPPING)
            ->set('depId', self::DEP_LAIN)
            ->assertDontSee(self::PESAN_TANPA_MAPPING);
    }

    public function test_user_dengan_departemen_tidak_melihat_pesan_belum_ada_mapping(): void
    {
        Livewire::actingAs($this->createUser())
            ->test(IndikatorMutu::class)
            ->call('loadProperties')
            ->assertDontSee(self::PESAN_TANPA_MAPPING);
    }

    public function test_user_bisa_memfilter_indikator_berdasarkan_departemen(): void
    {
        $this->indikator('Indikator Departemen Sendiri');
        $this->indikator('Indikator Departemen Lain', self::DEP_LAIN);

        Livewire::actingAs($this->createUser())
            ->test(IndikatorMutu::class)
            ->call('loadProperties')
            ->set('depId', self::DEP_LAIN)
            ->assertSee('Indikator Departemen Lain')
            ->assertDontSee('Indikator Departemen Sendiri');
    }

    public function test_user_bisa_mencari_indikator_berdasarkan_judul_profil(): void
    {
        $this->indikator('Kepatuhan Cuci Tangan');
        $this->indikator('Waktu Tunggu Rawat Jalan');

        Livewire::actingAs($this->createUser())
            ->test(IndikatorMutu::class)
            ->call('loadProperties')
            ->set('cari', 'cuci')
            ->assertSee('Kepatuhan Cuci Tangan')
            ->assertDontSee('Waktu Tunggu Rawat Jalan');
    }

    /**
     * @return string[][]
     */
    private function isiExport(?string $depId = null): array
    {
        $component = Livewire::actingAs($this->createUser())
            ->test(IndikatorMutu::class)
            ->set('depId', $depId ?? '')
            ->call('beginExcelExport')
            ->assertFileDownloaded();

        $dir = storage_path('framework/testing/export-mutu');
        File::ensureDirectoryExists($dir);
        File::put($dir.'/export.xlsx', base64_decode(data_get($component->lastResponse, 'original.effects.download.content')));

        $rows = (new Excel(['path' => $dir]))->openFile('export.xlsx')->openSheet()->getSheetData();

        // Nama file export berbasis detik; hapus agar test berikutnya tidak membaca file yang sama.
        File::deleteDirectory($dir);
        File::delete(storage_path('app/public/excel/'.data_get($component->lastResponse, 'original.effects.download.name')));

        return $rows;
    }

    public function test_export_tanpa_filter_berisi_indikator_departemen_user_seperti_layar(): void
    {
        $this->indikator('Indikator Departemen Sendiri');
        $this->indikator('Indikator Departemen Lain', self::DEP_LAIN);

        $isi = collect($this->isiExport())->flatten()->implode('|');

        $this->assertStringContainsString('Indikator Departemen Sendiri', $isi);
        $this->assertStringNotContainsString('Indikator Departemen Lain', $isi);
        $this->assertStringContainsString('DEPARTEMEN: BAGIAN IT/PROGRAMER/EDP', $isi);
    }

    public function test_export_dengan_filter_departemen_berisi_indikator_departemen_itu(): void
    {
        $this->indikator('Indikator Departemen Sendiri');
        $this->indikator('Indikator Departemen Lain', self::DEP_LAIN);

        $isi = collect($this->isiExport(self::DEP_LAIN))->flatten()->implode('|');

        $this->assertStringContainsString('Indikator Departemen Lain', $isi);
        $this->assertStringNotContainsString('Indikator Departemen Sendiri', $isi);
        $this->assertStringContainsString('DEPARTEMEN: ADMISSION', $isi);
    }

    public function test_kolom_export_sejajar_dengan_header(): void
    {
        $indicator = $this->indikator('Indikator Export');

        $rows = collect($this->isiExport())->map(fn (array $row) => array_values(array_filter($row, fn ($v) => $v !== '')));
        $header = $rows->search(fn (array $row) => ($row[0] ?? null) === 'ID');

        $this->assertSame(['ID', 'Indikator', 'Departemen', 'Standar', 'PJ', 'Status'], $rows[$header]);
        $this->assertSame('Indikator Export', $rows[$header + 1][1]);
        $this->assertSame('Bagian IT/Programer/EDP', $rows[$header + 1][2]);
        $this->assertSame((string) $indicator->person_in_charge, $rows[$header + 1][4]);
    }

    public function test_detail_menampilkan_record_dalam_rentang_tanggal_secara_berurutan(): void
    {
        $indicator = $this->indikator('Indikator Detail');
        $lain = $this->indikator('Indikator Lain');

        foreach (['2026-03-20', '2026-03-05', '2026-03-12', '2026-02-27', '2026-04-01'] as $date) {
            QualityIndicatorRecordFactory::new()->create(['indicator_id' => $indicator->id, 'recorded_date' => $date]);
        }

        QualityIndicatorRecordFactory::new()->create(['indicator_id' => $lain->id, 'recorded_date' => '2026-03-15']);

        $component = Livewire::actingAs($this->createUser())
            ->test(DetailIndikatorMutu::class, ['indicatorId' => $indicator->id])
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31')
            ->call('loadProperties')
            ->assertSeeInOrder(['05-03-2026', '12-03-2026', '20-03-2026'])
            ->assertDontSee('27-02-2026')
            ->assertDontSee('01-04-2026')
            ->assertDontSee('15-03-2026');

        $this->assertSame(
            ['2026-03-05', '2026-03-12', '2026-03-20'],
            $component->instance()->records->pluck('recorded_date')->map(fn ($d) => carbon($d)->format('Y-m-d'))->all()
        );
    }

    public function test_admin_bisa_menghapus_indikator_dari_halaman_detail(): void
    {
        $indicator = $this->indikator('Indikator Dihapus');

        Livewire::actingAs($this->createUser())
            ->test(DetailIndikatorMutu::class, ['indicatorId' => $indicator->id])
            ->call('delete')
            ->assertRedirect(route('admin.mutu.indikator-mutu'));

        $this->assertDatabaseMissing('quality_indicators', ['id' => $indicator->id], 'mysql_smc');
    }
}
