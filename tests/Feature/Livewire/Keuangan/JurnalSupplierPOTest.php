<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\JurnalSupplierPO;
use App\Models\Keuangan\Jurnal\JurnalMedis;
use App\Models\Keuangan\Jurnal\JurnalNonMedis;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: pulling supplier-payment journal entries out of mysql_sik's raw
 * jurnal.keterangan text into the local jurnal_medis/jurnal_non_medis cache
 * tables tarikDataTerbaru() reads from.
 *
 * refreshModel() (on both JurnalMedis and JurnalNonMedis) is entirely local -
 * it parses a specific keterangan sentence shape and incrementally pulls rows
 * newer than the latest waktu_jurnal already cached, no external system
 * involved. That parsing (BATAL prefix, no_faktur/petugas extraction, the
 * watermark dedup, and non-medis's extra "adjustmen" exclusion) is real logic
 * worth exercising directly; the report display itself (jurnalPenerimaanBarang(),
 * joining across half a dozen mysql_sik tables) gets only a mount-level smoke
 * check here.
 */
class JurnalSupplierPOTest extends TestCase
{
    private const PERMISSION = 'keuangan.jurnal-po-supplier.read';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');
        $smc = DB::connection('mysql_smc');

        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $smc->table('jurnal_medis')->where('no_jurnal', 'like', 'UJI%')->delete();
        $smc->table('jurnal_non_medis')->where('no_jurnal', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function jurnal(string $noJurnal, string $tanggal, string $jam, string $keterangan): void
    {
        DB::connection('mysql_sik')->table('jurnal')->insert([
            'no_jurnal' => $noJurnal, 'no_bukti' => 'BUKTI-'.$noJurnal, 'tgl_jurnal' => $tanggal,
            'jam_jurnal' => $jam, 'jenis' => 'U', 'keterangan' => $keterangan,
        ]);
    }

    private function report()
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION], '99999901');

        return Livewire::actingAs($petugas)->test(JurnalSupplierPO::class);
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
     *
     * A matching medis payoff entry and a matching non-medis payoff entry
     * each land in their own cache table, with no_faktur and the petugas name
     * pulled out of the keterangan sentence.
     */
    public function pulls_matching_entries_into_the_medis_and_non_medis_caches(): void
    {
        $this->jurnal('UJI-M1', '2026-03-05', '08:00:00', 'BAYAR PELUNASAN HUTANG OBAT/BHP/ALKES NO.FAKTUR FKT-M1, OLEH Petugas Uji');
        $this->jurnal('UJI-N1', '2026-03-05', '08:05:00', 'BAYAR PELUNASAN HUTANG BARANG NON MEDIS NO.FAKTUR FKT-N1, OLEH Petugas Uji');
        // Unrelated jurnal text must not be picked up by either cache.
        $this->jurnal('UJI-X1', '2026-03-05', '08:10:00', 'SETOR TUNAI KE BANK');

        $this->report()->call('tarikDataTerbaru');

        $medis = JurnalMedis::where('no_jurnal', 'UJI-M1')->first();
        $this->assertNotNull($medis);
        $this->assertSame('FKT-M1', $medis->no_faktur);
        $this->assertSame('Petugas Uji', $medis->nik);
        $this->assertSame('Sudah', $medis->status);

        $nonMedis = JurnalNonMedis::where('no_jurnal', 'UJI-N1')->first();
        $this->assertNotNull($nonMedis);
        $this->assertSame('FKT-N1', $nonMedis->no_faktur);
        $this->assertSame('Petugas Uji', $nonMedis->nik);

        $this->assertNull(JurnalMedis::where('no_jurnal', 'UJI-X1')->first());
        $this->assertNull(JurnalNonMedis::where('no_jurnal', 'UJI-X1')->first());
    }

    /**
     * @test
     */
    public function marks_a_batal_entry_and_still_extracts_its_no_faktur_and_petugas(): void
    {
        $this->jurnal('UJI-M2', '2026-03-05', '09:00:00', 'BATAL BAYAR PELUNASAN HUTANG OBAT/BHP/ALKES NO.FAKTUR FKT-M2, OLEH Petugas Batal');

        $this->report()->call('tarikDataTerbaru');

        $medis = JurnalMedis::where('no_jurnal', 'UJI-M2')->first();
        $this->assertSame('Batal', $medis->status);
        $this->assertSame('FKT-M2', $medis->no_faktur);
        $this->assertSame('Petugas Batal', $medis->nik);
    }

    /**
     * @test
     *
     * refreshModel() only pulls jurnal rows newer than the latest
     * waktu_jurnal already cached - an entry at exactly that watermark must
     * not be re-pulled (no duplicate), while a genuinely newer one is.
     */
    public function does_not_repull_an_entry_already_at_the_watermark(): void
    {
        JurnalMedis::insert([
            'no_jurnal' => 'UJI-OLD', 'waktu_jurnal' => '2026-03-01 08:00:00',
            'no_faktur' => 'FKT-OLD', 'status' => 'Sudah', 'ket' => 'sudah tercatat', 'nik' => 'Lama',
        ]);

        // Same instant as the watermark - already accounted for.
        $this->jurnal('UJI-SAME', '2026-03-01', '08:00:00', 'BAYAR PELUNASAN HUTANG OBAT/BHP/ALKES NO.FAKTUR FKT-SAME, OLEH Petugas Uji');
        // Genuinely newer - must be pulled.
        $this->jurnal('UJI-NEW', '2026-03-02', '09:00:00', 'BAYAR PELUNASAN HUTANG OBAT/BHP/ALKES NO.FAKTUR FKT-NEW, OLEH Petugas Uji');

        $this->report()->call('tarikDataTerbaru');

        $this->assertNull(JurnalMedis::where('no_jurnal', 'UJI-SAME')->first());
        $this->assertNotNull(JurnalMedis::where('no_jurnal', 'UJI-NEW')->first());
        $this->assertSame(2, JurnalMedis::where('no_jurnal', 'like', 'UJI%')->count());
    }

    /**
     * @test
     *
     * Non-medis has an extra exclusion the medis side doesn't: an otherwise
     * matching sentence mentioning "adjustmen" is skipped entirely.
     */
    public function excludes_non_medis_entries_mentioning_adjustmen(): void
    {
        $this->jurnal('UJI-ADJ', '2026-03-05', '10:00:00', 'BAYAR PELUNASAN HUTANG BARANG NON MEDIS NO.FAKTUR FKT-ADJ, OLEH Petugas Uji adjustmen stok');

        $this->report()->call('tarikDataTerbaru');

        $this->assertNull(JurnalNonMedis::where('no_jurnal', 'UJI-ADJ')->first());
    }
}
