<?php

namespace Tests\Feature\Livewire\Perawatan;

use App\Livewire\Pages\Perawatan\DaftarPasienRanap;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the inpatient list and its "update room price" action.
 *
 * updateHargaKamar() is this page's real write method - the "UbahHargaKamarInap"
 * modal (app/Livewire/Pages/Perawatan/Modal/UbahHargaKamarInap.php, flagged as
 * dead/empty scaffolding in Batch 8) never actually implemented this; the
 * working version lives here instead, same shape as ValidasiPiutang's action
 * really living on AccountReceivable.
 */
class DaftarPasienRanapTest extends TestCase
{
    private const PERMISSION = 'perawatan.daftar-pasien-ranap.read';

    private const UPDATE_PERMISSION = 'perawatan.daftar-pasien-ranap.update-harga-kamar';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('kamar_inap')->where('no_rawat', 'like', 'UJI%')->delete();
        $sik->table('kamar')->where('kd_kamar', 'UJIK1')->delete();
        $sik->table('bangsal')->where('kd_bangsal', 'UJIB1')->delete();

        parent::tearDown();
    }

    private function pasienRanap(string $nomor, string $tglMasuk, string $jamMasuk, float $trfKamar): void
    {
        $sik = DB::connection('mysql_sik');
        $noRekamMedis = 'UJI-RM'.$nomor;
        $noRawat = 'UJI/'.$nomor;

        if (! $sik->table('bangsal')->where('kd_bangsal', 'UJIB1')->exists()) {
            $sik->table('bangsal')->insert(['kd_bangsal' => 'UJIB1', 'nm_bangsal' => 'Bangsal Uji', 'status' => '1']);
        }

        if (! $sik->table('kamar')->where('kd_kamar', 'UJIK1')->exists()) {
            $sik->table('kamar')->insert([
                'kd_kamar' => 'UJIK1', 'kd_bangsal' => 'UJIB1', 'trf_kamar' => $trfKamar,
                'status' => 'ISI', 'kelas' => 'Kelas 1', 'statusdata' => '1',
            ]);
        }

        $this->createPasien($noRekamMedis, 'Pasien '.$nomor);
        $this->createRegistrasi($noRawat, $noRekamMedis, $tglMasuk, 'Ranap');

        $sik->table('kamar_inap')->insert([
            'no_rawat' => $noRawat, 'kd_kamar' => 'UJIK1', 'trf_kamar' => $trfKamar,
            'tgl_masuk' => $tglMasuk, 'jam_masuk' => $jamMasuk, 'lama' => 1,
            'ttl_biaya' => $trfKamar, 'stts_pulang' => '-',
        ]);
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION, self::UPDATE_PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(DaftarPasienRanap::class);
    }

    /**
     * @test
     */
    public function mounts_without_error(): void
    {
        $this->report()->assertOk();
    }

    /**
     * @test
     */
    public function search_does_not_crash(): void
    {
        $this->report()->set('cari', 'pasien')->assertOk();
    }

    /**
     * @test
     */
    public function refuses_to_update_the_room_price_without_permission(): void
    {
        $this->pasienRanap('01', now()->toDateString(), '09:00:00', 100000);

        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        Livewire::actingAs($petugas)
            ->test(DaftarPasienRanap::class)
            ->call('updateHargaKamar', 'UJI/01', 'UJIK1', now()->toDateString(), '09:00:00', 150000, 2)
            ->assertSee('Anda tidak diizinkan untuk melakukan tindakan ini!');

        $this->assertSame(100000.0, DB::connection('mysql_sik')->table('kamar_inap')->where('no_rawat', 'UJI/01')->value('trf_kamar'));
    }

    /**
     * @test
     */
    public function refuses_a_negative_room_price(): void
    {
        $this->pasienRanap('02', now()->toDateString(), '09:00:00', 100000);

        $this->report()
            ->call('updateHargaKamar', 'UJI/02', 'UJIK1', now()->toDateString(), '09:00:00', -1, 2)
            ->assertSee('Ada data salah, silahkan dicek input anda.');

        $this->assertSame(100000.0, DB::connection('mysql_sik')->table('kamar_inap')->where('no_rawat', 'UJI/02')->value('trf_kamar'));
    }

    /**
     * @test
     *
     * ttl_biaya is recomputed as hargaKamarBaru * lamaInap, not carried over
     * from the old total.
     */
    public function updates_the_room_price_and_recomputes_the_total(): void
    {
        $this->pasienRanap('03', now()->toDateString(), '09:00:00', 100000);

        $this->report()
            ->call('updateHargaKamar', 'UJI/03', 'UJIK1', now()->toDateString(), '09:00:00', 150000, 3)
            ->assertSee('Harga kamar berhasil diupdate!');

        $row = DB::connection('mysql_sik')->table('kamar_inap')->where('no_rawat', 'UJI/03')->first();

        $this->assertSame(150000.0, $row->trf_kamar);
        $this->assertSame(3.0, $row->lama);
        $this->assertSame(450000.0, $row->ttl_biaya);
    }
}
