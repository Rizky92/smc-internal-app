<?php

namespace Tests\Feature\Mutu;

use App\Models\Quality\QualityIndicator;
use App\Models\Quality\QualityIndicatorCategory;
use App\Models\Quality\QualityIndicatorProfile;
use Database\Factories\Quality\QualityIndicatorCategoryFactory;
use Database\Factories\Quality\QualityIndicatorFactory;
use Database\Factories\Quality\QualityIndicatorProfileFactory;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditProfilTest extends MutuTestCase
{
    private function audit(): string
    {
        $this->assertSame(0, Artisan::call('mutu:audit-profil'));

        return Artisan::output();
    }

    /**
     * Teks satu bagian laporan, dari judulnya sampai judul bagian berikutnya.
     */
    private function bagian(string $output, string $judul): string
    {
        $this->assertStringContainsString("== {$judul} ==", $output);

        return Str::before(Str::after($output, "== {$judul} =="), "\n== ");
    }

    public function test_profil_dengan_periode_analisis_di_luar_1_3_6_12_dilaporkan(): void
    {
        QualityIndicatorProfileFactory::new()->create(['title' => 'Audit Periode Dua', 'analysis_period' => 2]);
        QualityIndicatorProfileFactory::new()->create(['title' => 'Audit Periode Kosong', 'analysis_period' => null]);
        QualityIndicatorProfileFactory::new()->create(['title' => 'Audit Periode Triwulan', 'analysis_period' => 3]);

        $bagian = $this->bagian($this->audit(), 'Periode analisis di luar 1/3/6/12');

        $this->assertStringContainsString('Audit Periode Dua', $bagian);
        $this->assertStringContainsString('Audit Periode Kosong', $bagian);
        $this->assertStringNotContainsString('Audit Periode Triwulan', $bagian);
    }

    public function test_distribusi_periode_analisis_memuat_nilai_kosong_dan_nilai_tidak_baku(): void
    {
        QualityIndicatorProfileFactory::new()->create(['analysis_period' => 7]);
        QualityIndicatorProfileFactory::new()->create(['analysis_period' => null]);

        $bagian = $this->bagian($this->audit(), 'Distribusi periode analisis');

        $this->assertStringContainsString('(kosong)', $bagian);
        $this->assertMatchesRegularExpression('/\|\s*7\s*\|/', $bagian);
    }

    public function test_setiap_kategori_dicetak_beserta_profil_dan_indikatornya(): void
    {
        $kategori = QualityIndicatorCategoryFactory::new()->create(['name' => 'Kategori Audit Campuran']);

        $nasional = QualityIndicatorProfileFactory::new()->create([
            'quality_indicator_category_id' => $kategori->id,
            'title'                         => 'Audit Kepatuhan Kebersihan Tangan',
        ]);
        $unit = QualityIndicatorProfileFactory::new()->create([
            'quality_indicator_category_id' => $kategori->id,
            'title'                         => 'Audit Respon Time Unit',
        ]);
        QualityIndicatorProfileFactory::new()->create([
            'quality_indicator_category_id' => $kategori->id,
            'title'                         => 'Audit Profil Tanpa Indikator',
        ]);

        QualityIndicatorFactory::new()->create(['quality_indicator_profile_id' => $nasional->id, 'dep_id' => 'IT']);
        QualityIndicatorFactory::new()->create(['quality_indicator_profile_id' => $unit->id, 'dep_id' => 'ADM']);

        $bagian = $this->bagian($this->audit(), 'Kategori beserta indikatornya');
        $kategoriIni = Str::before(Str::after($bagian, 'Kategori Audit Campuran'), "\nKategori: ");

        $this->assertStringContainsString('Audit Kepatuhan Kebersihan Tangan', $kategoriIni);
        $this->assertStringContainsString('Audit Respon Time Unit', $kategoriIni);
        $this->assertStringContainsString('Audit Profil Tanpa Indikator', $kategoriIni);
        $this->assertStringContainsString('IT', $kategoriIni);
        $this->assertStringContainsString('ADM', $kategoriIni);
    }

    public function test_kategori_tanpa_profil_tetap_dicetak(): void
    {
        QualityIndicatorCategoryFactory::new()->create(['name' => 'Kategori Audit Kosong']);

        $bagian = $this->bagian($this->audit(), 'Kategori beserta indikatornya');
        $kategoriIni = Str::before(Str::after($bagian, 'Kategori Audit Kosong'), "\nKategori: ");

        $this->assertStringContainsString('tidak ada profil', $kategoriIni);
    }

    public function test_indikator_tanpa_penanggung_jawab_dilaporkan(): void
    {
        foreach (['Audit PJ Null' => null, 'Audit PJ Kosong' => '', 'Audit PJ Ada' => 'Ka. IGD'] as $title => $pj) {
            QualityIndicatorFactory::new()->create([
                'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create(['title' => $title]),
                'person_in_charge'             => $pj,
            ]);
        }

        $bagian = $this->bagian($this->audit(), 'Indikator tanpa penanggung jawab');

        $this->assertStringContainsString('Audit PJ Null', $bagian);
        $this->assertStringContainsString('Audit PJ Kosong', $bagian);
        $this->assertStringNotContainsString('Audit PJ Ada', $bagian);
    }

    public function test_indikator_tanpa_pic_dilaporkan(): void
    {
        foreach (['Audit PIC Kosong' => null, 'Audit PIC Ada' => 'MUTU-PIC-01'] as $title => $nik) {
            QualityIndicatorFactory::new()->create([
                'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create(['title' => $title]),
                'pic_nik'                      => $nik,
            ]);
        }

        $bagian = $this->bagian($this->audit(), 'Indikator tanpa PIC (pic_nik)');

        $this->assertStringContainsString('Audit PIC Kosong', $bagian);
        $this->assertStringNotContainsString('Audit PIC Ada', $bagian);
    }

    public function test_indikator_dengan_pic_tidak_aktif_dilaporkan_beserta_statusnya(): void
    {
        $this->createUser('MUTU-PIC-KELUAR', []);
        $this->createUser('MUTU-PIC-CUTI', []);
        $this->createUser('MUTU-PIC-AKTIF', []);
        DB::connection('mysql_sik')->table('pegawai')->where('nik', 'MUTU-PIC-KELUAR')->update(['stts_aktif' => 'KELUAR']);
        DB::connection('mysql_sik')->table('pegawai')->where('nik', 'MUTU-PIC-CUTI')->update(['stts_aktif' => 'CUTI']);

        $pic = [
            'Audit PIC Keluar'          => 'MUTU-PIC-KELUAR',
            'Audit PIC Cuti'            => 'MUTU-PIC-CUTI',
            'Audit PIC Tidak Ditemukan' => 'MUTU-PIC-HILANG',
            'Audit PIC Masih Aktif'     => 'MUTU-PIC-AKTIF',
            'Audit PIC Belum Diisi'     => null,
        ];

        foreach ($pic as $title => $nik) {
            QualityIndicatorFactory::new()->create([
                'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create(['title' => $title]),
                'pic_nik'                      => $nik,
            ]);
        }

        $bagian = $this->bagian($this->audit(), 'Indikator dengan PIC tidak aktif');

        $this->assertMatchesRegularExpression('/Audit PIC Keluar.*KELUAR/', $bagian);
        $this->assertMatchesRegularExpression('/Audit PIC Cuti.*CUTI/', $bagian);
        $this->assertMatchesRegularExpression('/Audit PIC Tidak Ditemukan.*tidak ditemukan/', $bagian);
        $this->assertStringNotContainsString('Audit PIC Masih Aktif', $bagian);
        $this->assertStringNotContainsString('Audit PIC Belum Diisi', $bagian);
    }

    public function test_standard_yang_gagal_diparse_dilaporkan_dengan_teks_aslinya(): void
    {
        QualityIndicatorProfileFactory::new()->create(['title' => 'Audit Standar Teks', 'standard' => 'sesuai SPO']);
        QualityIndicatorProfileFactory::new()->create(['title' => 'Audit Standar Angka', 'standard' => '≥ 85 %']);

        $bagian = $this->bagian($this->audit(), 'Standar yang gagal diparse');

        $this->assertMatchesRegularExpression('/Audit Standar Teks.*sesuai SPO/', $bagian);
        $this->assertStringNotContainsString('Audit Standar Angka', $bagian);
    }

    public function test_profil_tanpa_operator_atau_nilai_target_dilaporkan(): void
    {
        QualityIndicatorProfileFactory::new()->create(['title' => 'Audit Tanpa Operator', 'target_operator' => null, 'target_value' => 80]);
        QualityIndicatorProfileFactory::new()->create(['title' => 'Audit Tanpa Nilai', 'target_operator' => 'gte', 'target_value' => null]);
        QualityIndicatorProfileFactory::new()->create(['title' => 'Audit Target Lengkap', 'target_operator' => 'lte', 'target_value' => 5]);

        $output = $this->audit();
        $tanpaOperator = $this->bagian($output, 'Profil tanpa operator target');
        $tanpaNilai = $this->bagian($output, 'Profil tanpa nilai target');

        $this->assertStringContainsString('Audit Tanpa Operator', $tanpaOperator);
        $this->assertStringNotContainsString('Audit Tanpa Nilai', $tanpaOperator);
        $this->assertStringNotContainsString('Audit Target Lengkap', $tanpaOperator);

        $this->assertStringContainsString('Audit Tanpa Nilai', $tanpaNilai);
        $this->assertStringNotContainsString('Audit Tanpa Operator', $tanpaNilai);
        $this->assertStringNotContainsString('Audit Target Lengkap', $tanpaNilai);
    }

    public function test_audit_tidak_mengubah_data(): void
    {
        QualityIndicatorFactory::new()->create([
            'quality_indicator_profile_id' => QualityIndicatorProfileFactory::new()->create(['analysis_period' => 5, 'standard' => 'tidak ada']),
            'person_in_charge'             => null,
        ]);

        $sebelum = [
            QualityIndicatorCategory::count(),
            QualityIndicatorProfile::count(),
            QualityIndicator::count(),
            QualityIndicatorProfile::max('updated_at'),
            QualityIndicator::max('updated_at'),
        ];

        $this->audit();

        $this->assertSame($sebelum, [
            QualityIndicatorCategory::count(),
            QualityIndicatorProfile::count(),
            QualityIndicator::count(),
            QualityIndicatorProfile::max('updated_at'),
            QualityIndicator::max('updated_at'),
        ]);
    }
}
