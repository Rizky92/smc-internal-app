<?php

namespace Tests\Feature\Livewire\Informasi;

use App\Livewire\Pages\Informasi\JadwalDokter;
use App\Models\Antrian\Jadwal;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: today's doctor schedule board.
 *
 * Real logic worth verifying directly: when a doctor has two jadwal rows for
 * the same day/poli (a duplicate), Jadwal::hitungTotalRegistrasi() splits the
 * day's registration count between them - the earlier slot capped at its own
 * kuota, the later slot showing whatever's left over (never negative). Both
 * JadwalDokter and DisplayJadwalDokter call this exact same static method,
 * so it's covered once here against the model directly rather than twice
 * through two Livewire components.
 */
class JadwalDokterTest extends TestCase
{
    private const PERMISSION = 'informasi.jadwal-dokter.read';

    /**
     * The doctor's own identity, kept separate from whichever NIK logs in to
     * view the report. dokter.kd_dokter is a foreign key into pegawai.nik, so
     * this rides on the same reserved-NIK fixture petugasWithPermissions()
     * uses - petugas cleanup in TestCase::tearDown() already removes the
     * pegawai row this depends on.
     */
    private const KODE_DOKTER = '99999902';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('reg_periksa')->where('no_rawat', 'like', 'UJI%')->delete();
        $sik->table('jadwal')->where('kd_dokter', self::KODE_DOKTER)->delete();
        $sik->table('dokter')->where('kd_dokter', self::KODE_DOKTER)->delete();

        parent::tearDown();
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(JadwalDokter::class);
    }

    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        $this->report()
            ->call('loadProperties')
            ->assertOk();
    }

    /**
     * @test
     */
    public function semua_poli_toggle_and_search_do_not_crash(): void
    {
        $test = $this->report()->call('loadProperties');

        $test->set('semuaPoli', true)->assertOk();
        $test->set('cari', 'dokter')->assertOk();
    }

    /**
     * @test
     *
     *   Slot 1 (earlier jam_mulai), kuota 2
     *   Slot 2 (later jam_mulai)
     *   5 patients registered that day -> slot 1 shows min(5, 2) = 2,
     *   slot 2 shows the remainder max(0, 5 - 2) = 3.
     */
    public function splits_the_days_registration_count_between_duplicate_slots_by_quota(): void
    {
        $this->petugasWithPermissions([], self::KODE_DOKTER);

        $sik = DB::connection('mysql_sik');
        $kdPoli = $sik->table('poliklinik')->value('kd_poli');
        $tanggal = now()->toDateString();

        $sik->table('dokter')->insert(['kd_dokter' => self::KODE_DOKTER, 'email' => 'uji@test.invalid', 'status' => '1']);

        $sik->table('jadwal')->insert([
            ['kd_dokter' => self::KODE_DOKTER, 'hari_kerja' => 'SENIN', 'jam_mulai' => '08:00:00', 'jam_selesai' => '10:00:00', 'kd_poli' => $kdPoli, 'kuota' => 2],
            ['kd_dokter' => self::KODE_DOKTER, 'hari_kerja' => 'SENIN', 'jam_mulai' => '13:00:00', 'jam_selesai' => '15:00:00', 'kd_poli' => $kdPoli, 'kuota' => 10],
        ]);

        for ($i = 1; $i <= 5; $i++) {
            $noRekamMedis = 'UJI-RM'.$i;
            $noRawat = 'UJI/'.$i;

            $this->createPasien($noRekamMedis, 'Pasien '.$i);
            $this->createRegistrasi($noRawat, $noRekamMedis, $tanggal);
            $sik->table('reg_periksa')->where('no_rawat', $noRawat)->update([
                'kd_dokter' => self::KODE_DOKTER, 'kd_poli' => $kdPoli,
            ]);
        }

        [$slot1, $slot2] = Jadwal::hitungTotalRegistrasi(self::KODE_DOKTER, $kdPoli, 'SENIN', $tanggal);

        $this->assertSame(2, $slot1);
        $this->assertSame(3, $slot2);
    }
}
