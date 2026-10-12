<?php

namespace Tests\Feature\Livewire\Keuangan;

use App\Livewire\Pages\Keuangan\JurnalPosting;
use App\Models\Keuangan\Jurnal\PostingJurnal;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the posted-jurnal list and its "Cetak" redirect.
 *
 * jurnalPosting() only surfaces a jurnal that has a matching row in
 * mysql_smc's posting_jurnal - a jurnal recorded but never posted must not
 * appear here, since this page is specifically "what's already posted".
 * cetak() then base64-encodes exactly the no_jurnal list the current filter
 * produced, so the encoded payload has to match what the filtered list
 * actually contains, not the whole table. The write path itself is already
 * covered by InputJurnalPostingTest.
 */
class JurnalPostingTest extends TestCase
{
    private const AWAL = '2026-03-01';

    private const AKHIR = '2026-03-31';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');
        $smc = DB::connection('mysql_smc');

        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI%')->delete();
        $smc->table('posting_jurnal')->where('no_jurnal', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    private function jurnal(string $noJurnal, string $tanggal, bool $posted): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('jurnal')->insert([
            'no_jurnal' => $noJurnal, 'no_bukti' => 'BUKTI-'.$noJurnal, 'tgl_jurnal' => $tanggal,
            'jam_jurnal' => '09:00:00', 'jenis' => 'U', 'keterangan' => 'Jurnal '.$noJurnal,
        ]);

        $sik->table('detailjurnal')->insert([
            'no_jurnal' => $noJurnal, 'kd_rek' => 'UJI.1', 'debet' => 100000, 'kredit' => 0,
        ]);

        if ($posted) {
            PostingJurnal::create(['no_jurnal' => $noJurnal, 'tgl_jurnal' => $tanggal]);
        }
    }

    /**
     * @test
     */
    public function lists_only_journals_that_have_actually_been_posted(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        DB::connection('mysql_sik')->table('rekening')->insert([
            'kd_rek' => 'UJI.1', 'nm_rek' => 'Kas Uji', 'tipe' => 'R', 'balance' => 'D', 'level' => '1',
        ]);

        $this->jurnal('UJI-001', '2026-03-05', posted: true);
        $this->jurnal('UJI-002', '2026-03-06', posted: false);

        Livewire::actingAs($petugas)
            ->test(JurnalPosting::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->call('loadProperties')
            ->assertSee('UJI-001')
            ->assertDontSee('UJI-002');
    }

    /**
     * @test
     */
    public function cetak_redirects_with_only_the_filtered_journals_encoded(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        DB::connection('mysql_sik')->table('rekening')->insert([
            'kd_rek' => 'UJI.1', 'nm_rek' => 'Kas Uji', 'tipe' => 'R', 'balance' => 'D', 'level' => '1',
        ]);

        $this->jurnal('UJI-001', '2026-03-05', posted: true);
        // Posted, but outside the filtered period - must not be encoded.
        $this->jurnal('UJI-002', '2026-02-05', posted: true);

        Livewire::actingAs($petugas)
            ->test(JurnalPosting::class)
            ->set('tglAwal', self::AWAL)
            ->set('tglAkhir', self::AKHIR)
            ->call('cetak')
            ->assertRedirect(route('admin.keuangan.cetak-posting-jurnal', [
                'data_jurnal' => base64_encode(json_encode(['UJI-001'])),
            ]));
    }
}
