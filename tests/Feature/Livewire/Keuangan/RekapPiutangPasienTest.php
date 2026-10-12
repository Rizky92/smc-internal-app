<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\RekapPiutangPasien;
use App\Models\Keuangan\PiutangPasien;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: this component has no route anywhere (grepped resources/views,
 * app/Livewire and routes/ - nothing references it, and there is no
 * "Route::get(...)" for it either) - but unlike the dead scaffolding found
 * in earlier batches (ModalHakAksesBaru, ModalTemplateBaru,
 * UbahHargaKamarInap), it is fully built: a real Blade view with real
 * columns, a real filter, and real arithmetic (sisa = sisapiutang - terbayar,
 * where terbayar is summed from bayar_piutang). It reads like a report that
 * was finished and then never wired to a route, rather than an abandoned
 * stub. Flagged for a decision (add the route, or confirm it's intentionally
 * retired) rather than assumed either way. Tested here as if it were live,
 * since Livewire::test() doesn't need a route to exercise a component.
 *
 * Also fixed in the same investigation: PiutangPasien::scopeRekapPiutangPasien()'s
 * empty-$tglAkhir fallback called now()->endOfMont() - a typo, not
 * endOfMonth() - which would fatal with a BadMethodCallException. This
 * component always supplies both dates via defaultValues(), so the fallback
 * is normally unreachable; fixed anyway and covered directly against the
 * model since the bug lives in the scope, not the component. Mutation-tested
 * by reverting the typo and confirming the new test errors identically.
 */
class RekapPiutangPasienTest extends TestCase
{
    private const PERMISSION = 'keuangan.rekap-piutang-pasien.read';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('bayar_piutang')->where('no_rawat', 'like', 'UJI%')->delete();
        $sik->table('piutang_pasien')->where('no_rawat', 'like', 'UJI%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function akun(): void
    {
        $sik = DB::connection('mysql_sik');

        if ($sik->table('rekening')->where('kd_rek', 'UJI.9')->exists()) {
            return;
        }

        $sik->table('rekening')->insert([
            'kd_rek' => 'UJI.9', 'nm_rek' => 'Piutang Uji', 'tipe' => 'R', 'balance' => 'D', 'level' => '1',
        ]);
    }

    private function piutang(string $nomor, string $tglPiutang, float $total, float $sisa, float $dibayar = 0): void
    {
        $sik = DB::connection('mysql_sik');
        $noRawat = 'UJI/'.$nomor;
        $noRekamMedis = 'UJI-RM'.$nomor;

        $this->createPasien($noRekamMedis, 'Pasien '.$nomor);
        $this->createRegistrasi($noRawat, $noRekamMedis, $tglPiutang);

        $sik->table('piutang_pasien')->insert([
            'no_rawat' => $noRawat, 'tgl_piutang' => $tglPiutang, 'no_rkm_medis' => $noRekamMedis,
            'status' => 'Belum Lunas', 'totalpiutang' => $total, 'uangmuka' => 0,
            'sisapiutang' => $sisa, 'tgltempo' => $tglPiutang,
        ]);

        if ($dibayar > 0) {
            $this->akun();

            $sik->table('bayar_piutang')->insert([
                'tgl_bayar' => $tglPiutang, 'no_rkm_medis' => $noRekamMedis, 'no_rawat' => $noRawat,
                'besar_cicilan' => $dibayar, 'catatan' => '-',
                'kd_rek' => 'UJI.9', 'kd_rek_kontra' => 'UJI.9',
                'diskon_piutang' => 0, 'kd_rek_diskon_piutang' => 'UJI.9',
                'tidak_terbayar' => 0, 'kd_rek_tidak_terbayar' => 'UJI.9',
            ]);
        }
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)
            ->test(RekapPiutangPasien::class)
            ->set('tglAwal', '2026-03-01')
            ->set('tglAkhir', '2026-03-31');
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
    public function search_does_not_crash(): void
    {
        $this->report()
            ->call('loadProperties')
            ->set('cari', 'pasien')
            ->assertOk();
    }

    /**
     * @test
     *
     * sisa is sisapiutang minus whatever bayar_piutang already covered - not
     * sisapiutang alone, and not totalpiutang minus terbayar.
     */
    public function computes_the_remaining_balance_after_what_has_already_been_paid(): void
    {
        $this->piutang('01', '2026-03-05', total: 500000, sisa: 300000, dibayar: 100000);

        $test = $this->report()->call('loadProperties');

        $row = collect($test->instance()->piutangPasien->items())->first();

        $this->assertSame(500000.0, (float) $row->total);
        $this->assertSame(100000.0, (float) $row->terbayar);
        $this->assertSame(200000.0, (float) $row->sisa);
    }

    /**
     * @test
     */
    public function totals_the_remaining_balance_across_every_row(): void
    {
        $this->piutang('02', '2026-03-05', total: 500000, sisa: 300000, dibayar: 100000);
        $this->piutang('03', '2026-03-06', total: 200000, sisa: 200000, dibayar: 0);

        $total = $this->report()->call('loadProperties')->instance()->totalTagihanPiutangPasien;

        $this->assertEqualsWithDelta(400000, $total, 0.01);
    }

    /**
     * @test
     *
     * Direct model test for the scope's empty-argument fallback, which the
     * component itself never triggers (it always supplies both dates).
     */
    public function scope_falls_back_to_the_current_month_when_no_dates_are_given(): void
    {
        $this->piutang('04', now()->toDateString(), total: 100000, sisa: 100000);

        $count = PiutangPasien::query()->rekapPiutangPasien('', '')->where('piutang_pasien.no_rawat', 'UJI/04')->count();

        $this->assertSame(1, $count);
    }
}
