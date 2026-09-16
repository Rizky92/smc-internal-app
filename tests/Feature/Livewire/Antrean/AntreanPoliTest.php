<?php

namespace Tests\Feature\Livewire\Antrean;

use App\Livewire\Pages\Antrean\AntreanPoli;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The public waiting-room display for one poliklinik.
 *
 * Reached at /antrean/{kd_poli}, so the identifier comes straight from the URL
 * and nothing guarantees it names a poli that exists — a clinic can be renamed
 * or retired in Khanza while a display in a corridor is still pointed at the old
 * code. Parameterised routes are excluded from RouteSweepTest, which is why this
 * screen needs its own test.
 */
class AntreanPoliTest extends TestCase
{
    /**
     * dokter.kd_dokter is a foreign key into pegawai.nik, so the doctor has to
     * be a real member of staff. Inside CreatesPetugas' reserved 9999990 range
     * so TestCase's own teardown reclaims the pegawai row.
     */
    private const KD_DOKTER = '99999903';

    private const NO_RAWAT = 'UJI/0001';

    private const NO_REKAM_MEDIS = 'UJI-RM01';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('antripoli')->where('no_rawat', self::NO_RAWAT)->delete();
        $sik->table('dokter')->where('kd_dokter', self::KD_DOKTER)->delete();
        $sik->table('poliklinik')->where('kd_poli', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function createPoliklinik(string $kdPoli, string $nama): void
    {
        DB::connection('mysql_sik')->table('poliklinik')->insert([
            'kd_poli'        => $kdPoli,
            'nm_poli'        => $nama,
            'registrasi'     => 0,
            'registrasilama' => 0,
            'status'         => '1',
        ]);
    }

    /**
     * A patient waiting at the front of one poli's queue: registered today
     * against a doctor in that poli, still "Belum", with an antripoli row at
     * status 1 ("being called").
     */
    private function antreanSiapDipanggil(string $kdPoli, string $namaPoli, string $namaPasien): void
    {
        $sik = DB::connection('mysql_sik');

        $this->createPoliklinik($kdPoli, $namaPoli);
        $this->petugasWithPermissions([], self::KD_DOKTER);

        $sik->table('dokter')->insert([
            'kd_dokter' => self::KD_DOKTER, 'nm_dokter' => 'Dokter Uji',
            'email'     => 'dokter.uji@test.invalid', 'status' => '1',
        ]);

        $this->createPasien(self::NO_REKAM_MEDIS, $namaPasien);

        $sik->table('reg_periksa')->insert([
            'no_reg'       => '001', 'no_rawat' => self::NO_RAWAT,
            'no_rkm_medis' => self::NO_REKAM_MEDIS, 'tgl_registrasi' => now()->toDateString(),
            'jam_reg'      => '09:00:00', 'kd_poli' => $kdPoli, 'kd_dokter' => self::KD_DOKTER,
            'kd_pj'        => $sik->table('penjab')->value('kd_pj'), 'stts' => 'Belum',
            'stts_daftar'  => 'Baru', 'status_lanjut' => 'Ralan',
            'status_bayar' => 'Belum Bayar', 'status_poli' => 'Baru',
        ]);

        $sik->table('antripoli')->insert([
            'kd_dokter' => self::KD_DOKTER, 'kd_poli' => $kdPoli,
            'no_rawat'  => self::NO_RAWAT, 'status' => '1',
        ]);
    }

    /**
     * @test
     *
     * Same shape as AntreanDiPanggil: the blade announces the patient straight
     * off event.detail.nm_pasien.toLowerCase(), so the fields have to reach the
     * browser as named parameters rather than one positional array.
     */
    public function announces_the_patient_with_the_fields_the_browser_reads(): void
    {
        $this->antreanSiapDipanggil('UJI01', 'Poli Uji Dalam', 'BUDI SANTOSO');

        Livewire::test(AntreanPoli::class, ['kd_poli' => 'UJI01'])
            ->call('call')
            ->assertDispatched(
                'play-voice',
                no_reg: '001',
                nm_pasien: 'BUDI SANTOSO',
                nm_poli: 'Poli Uji Dalam'
            );
    }

    /**
     * @test
     */
    public function shows_the_name_of_the_poliklinik(): void
    {
        $this->createPoliklinik('UJI01', 'Poli Uji Dalam');

        $this->withoutExceptionHandling();

        $this->get('/antrean/UJI01')
            ->assertOk()
            ->assertSee('Poli Uji Dalam');
    }

    /**
     * @test
     *
     * An unknown code must not take the display down. The header simply has no
     * name to show.
     */
    public function survives_a_kode_poli_that_does_not_exist(): void
    {
        $this->withoutExceptionHandling();

        $this->get('/antrean/TIDAKADA')->assertOk();
    }
}
