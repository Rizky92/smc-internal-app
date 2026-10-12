<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\JurnalPerbaikan;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the jurnal list this page hands to the UbahTanggalJurnal modal.
 *
 * jurnalUmum() requires a jurnal to have at least one detail line
 * (whereHas('detail')) - a jurnal recorded with no detail lines at all
 * (detailjurnal.kd_rek carries a hard foreign key into rekening, so "a
 * detail line pointing at a rekening that doesn't exist" can't actually be
 * constructed) must not appear here. The write path itself is already
 * covered by UbahTanggalJurnalTest.
 */
class JurnalPerbaikanTest extends TestCase
{
    private const AWAL = '2026-03-01';

    private const AKHIR = '2026-03-31';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function jurnal(string $noJurnal, string $tanggal, bool $withDetail): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('jurnal')->insert([
            'no_jurnal' => $noJurnal, 'no_bukti' => 'BUKTI-'.$noJurnal, 'tgl_jurnal' => $tanggal,
            'jam_jurnal' => '09:00:00', 'jenis' => 'U', 'keterangan' => 'Jurnal '.$noJurnal,
        ]);

        if ($withDetail) {
            $sik->table('detailjurnal')->insert([
                'no_jurnal' => $noJurnal, 'kd_rek' => 'UJI.1', 'debet' => 100000, 'kredit' => 0,
            ]);
        }
    }

    /**
     * @test
     */
    public function lists_only_journals_that_have_a_detail_line(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        DB::connection('mysql_sik')->table('rekening')->insert([
            'kd_rek' => 'UJI.1', 'nm_rek' => 'Kas Uji', 'tipe' => 'R', 'balance' => 'D', 'level' => '1',
        ]);

        $this->jurnal('UJI-001', '2026-03-05', withDetail: true);
        $this->jurnal('UJI-002', '2026-03-06', withDetail: false);

        Livewire::actingAs($petugas)
            ->test(JurnalPerbaikan::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->call('loadProperties')
            ->assertSee('UJI-001')
            ->assertDontSee('UJI-002');
    }
}
