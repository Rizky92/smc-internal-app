<?php

namespace Tests\Feature\Livewire\Antrean;

use App\Livewire\Pages\Antrean\AntreanDiPanggil;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The queue-calling display board, one per entrance.
 *
 * Its polling method used to be named call(), which sounds harmless but is
 * reserved on Livewire 3's $wire proxy — an alias for $wire's own internal
 * $call() helper (see WireShorthandReservedWordsTest for the full list and
 * the mechanism). wire:poll="call" never reached this method: it invoked
 * Livewire's helper with no arguments instead, which POSTs
 * {method: "undefined"} to the server on every tick. The result was a
 * screen that 500'd every two seconds while sitting idle on a wall-mounted
 * display — a JS-runtime bug invisible to Livewire::test(), which calls PHP
 * methods directly and never goes through the $wire proxy that broke.
 *
 * The method is now panggilAntrean(); this pins the rename and the
 * behaviour it's supposed to have (renders, and is a no-op re-entry guard
 * while a patient is already being called).
 */
class AntreanDiPanggilTest extends TestCase
{
    private const KD_PINTU = 'UJI-PINTU';

    private const KD_POLI = 'UJIP';

    /**
     * dokter.kd_dokter is a foreign key into pegawai.nik, so the doctor has to
     * be a real member of staff. Inside CreatesPetugas' reserved 9999990 range
     * so DuskTestCase/TestCase's own teardown reclaims the pegawai row.
     */
    private const KD_DOKTER = '99999903';

    private const NO_RAWAT = 'UJI/0001';

    private const NO_REKAM_MEDIS = 'UJI-RM01';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('antripintu_smc')->where('kd_pintu', self::KD_PINTU)->delete();
        $sik->table('set_pintu_smc')->where('kd_pintu', self::KD_PINTU)->delete();
        $sik->table('pintu_smc')->where('kd_pintu', self::KD_PINTU)->delete();
        $sik->table('jadwal')->where('kd_dokter', self::KD_DOKTER)->delete();
        $sik->table('dokter')->where('kd_dokter', self::KD_DOKTER)->delete();
        $sik->table('poliklinik')->where('kd_poli', self::KD_POLI)->delete();

        parent::tearDown();
    }

    /**
     * The whole join chain behind Pintu::antreanPerPintu(): a pintu wired to a
     * poli and a dokter, a patient registered today against that pair, and a
     * queue row for that visit sitting at status 1 ("being called").
     */
    private function antreanSiapDipanggil(string $namaPasien, string $namaPintu): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('poliklinik')->insert([
            'kd_poli'    => self::KD_POLI, 'nm_poli' => 'Poli Uji',
            'registrasi' => 0, 'registrasilama' => 0, 'status' => '1',
        ]);

        $this->petugasWithPermissions([], self::KD_DOKTER);

        $sik->table('dokter')->insert([
            'kd_dokter' => self::KD_DOKTER, 'nm_dokter' => 'Dokter Uji',
            'email'     => 'dokter.uji@test.invalid', 'status' => '1',
        ]);

        $sik->table('pintu_smc')->insert([
            'kd_pintu' => self::KD_PINTU, 'nm_pintu' => $namaPintu, 'status' => '1',
        ]);

        $sik->table('set_pintu_smc')->insert([
            'kd_pintu' => self::KD_PINTU, 'kd_dokter' => self::KD_DOKTER, 'kd_poli' => self::KD_POLI,
        ]);

        $sik->table('jadwal')->insert([
            'kd_dokter'  => self::KD_DOKTER, 'kd_poli' => self::KD_POLI,
            'hari_kerja' => 'SENIN', 'jam_mulai' => '08:00:00', 'jam_selesai' => '14:00:00',
        ]);

        $this->createPasien(self::NO_REKAM_MEDIS, $namaPasien);

        $sik->table('reg_periksa')->insert([
            'no_reg'       => '001', 'no_rawat' => self::NO_RAWAT,
            'no_rkm_medis' => self::NO_REKAM_MEDIS, 'tgl_registrasi' => now()->toDateString(),
            'jam_reg'      => '09:00:00', 'kd_poli' => self::KD_POLI, 'kd_dokter' => self::KD_DOKTER,
            'kd_pj'        => $sik->table('penjab')->value('kd_pj'), 'stts' => 'Belum',
            'stts_daftar'  => 'Baru', 'status_lanjut' => 'Ralan',
            'status_bayar' => 'Belum Bayar', 'status_poli' => 'Baru',
        ]);

        $sik->table('antripintu_smc')->insert([
            'kd_pintu' => self::KD_PINTU, 'no_rawat' => self::NO_RAWAT, 'status' => '1',
        ]);
    }

    /**
     * @test
     *
     * The blade reads the announcement straight off the browser event:
     * event.detail.nm_pasien.toLowerCase(). Livewire 3's dispatch() is
     * variadic, so handing it one associative array puts that array at key 0
     * and the listener sees {"0": {...}} - every field it wants is undefined,
     * and the first .toLowerCase() throws, killing the announcement for every
     * patient called at that entrance. The names have to arrive as named
     * parameters for event.detail to have them at the top level.
     */
    public function announces_the_patient_with_the_fields_the_browser_reads(): void
    {
        $this->antreanSiapDipanggil('BUDI SANTOSO', 'Pintu Uji Utara');

        Livewire::test(AntreanDiPanggil::class, ['kd_pintu' => self::KD_PINTU])
            ->call('panggilAntrean')
            ->assertDispatched(
                'play-voice',
                no_reg: '001',
                nm_pasien: 'BUDI SANTOSO',
                nm_pintu: 'Pintu Uji Utara'
            );
    }

    /**
     * @test
     */
    public function keeps_the_poll_directive_pointed_at_a_real_method(): void
    {
        $test = Livewire::test(AntreanDiPanggil::class, ['kd_pintu' => 'TIDAKADA']);

        $test->assertSee('wire:poll.2000ms.keep-alive="panggilAntrean"', false);
    }

    /**
     * @test
     */
    public function does_nothing_while_a_patient_is_already_being_called(): void
    {
        $test = Livewire::test(AntreanDiPanggil::class, ['kd_pintu' => 'TIDAKADA']);

        $test->set('isCalling', true);
        $test->set('antreanDipanggilSekarang', ['no_rawat' => 'UJI/1']);

        $test->call('panggilAntrean');

        $test->assertSet('antreanDipanggilSekarang', ['no_rawat' => 'UJI/1']);
    }

    /**
     * @return array<string, string>
     */
    private function statusAntrean(): array
    {
        return DB::connection('mysql_sik')->table('antripintu_smc')
            ->where('kd_pintu', self::KD_PINTU)
            ->pluck('status', 'no_rawat')
            ->all();
    }

    /**
     * @test
     *
     * When the announcement finishes the browser fires updateStatus. The patient
     * just called goes in (1 -> 2), and whoever was still marked as in the room
     * at this pintu comes out (2 -> 0) — a pintu has one patient inside at a
     * time, and the "sedang diperiksa" panel reads exactly that row.
     */
    public function finishing_the_announcement_moves_the_called_patient_into_the_room(): void
    {
        $this->antreanSiapDipanggil('BUDI SANTOSO', 'Pintu Uji');

        DB::connection('mysql_sik')->table('antripintu_smc')->insert([
            'kd_pintu' => self::KD_PINTU, 'no_rawat' => 'UJI/0009', 'status' => '2',
        ]);

        Livewire::test(AntreanDiPanggil::class, ['kd_pintu' => self::KD_PINTU])
            ->call('panggilAntrean')
            ->assertSet('isCalling', true)
            ->dispatch('updateStatus')
            ->assertSet('isCalling', false)
            ->assertSet('antreanDipanggilSekarang', null);

        $this->assertSame([self::NO_RAWAT => '2', 'UJI/0009' => '0'], $this->statusAntrean());
    }

    /**
     * @test
     *
     * Releasing the lock is what lets the next poll call the next patient. If it
     * stayed set, the display would announce one patient and then fall silent.
     */
    public function after_the_announcement_the_display_can_call_again(): void
    {
        $this->antreanSiapDipanggil('BUDI SANTOSO', 'Pintu Uji');

        $test = Livewire::test(AntreanDiPanggil::class, ['kd_pintu' => self::KD_PINTU])
            ->call('panggilAntrean')
            ->dispatch('updateStatus');

        DB::connection('mysql_sik')->table('antripintu_smc')
            ->where('no_rawat', self::NO_RAWAT)
            ->update(['status' => '1']);

        $test->call('panggilAntrean')
            ->assertSet('isCalling', true)
            ->assertDispatched('play-voice');
    }

    /**
     * @test
     *
     * A stray updateStatus with nothing being called — a second browser tab,
     * a reload mid-announcement — must not touch the queue.
     */
    public function an_update_with_nothing_being_called_changes_nothing(): void
    {
        $this->antreanSiapDipanggil('BUDI SANTOSO', 'Pintu Uji');

        Livewire::test(AntreanDiPanggil::class, ['kd_pintu' => self::KD_PINTU])
            ->dispatch('updateStatus')
            ->assertSet('isCalling', false);

        $this->assertSame([self::NO_RAWAT => '1'], $this->statusAntrean());
    }
}
