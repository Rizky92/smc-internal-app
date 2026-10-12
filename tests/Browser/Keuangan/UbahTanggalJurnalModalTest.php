<?php

namespace Tests\Browser\Keuangan;

use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * The only thing this covers that Livewire::test() (JurnalPerbaikanTest,
 * UbahTanggalJurnalTest) cannot: a real click on the "Edit" button opens the
 * Bootstrap modal via jQuery ('shown.bs.modal' -> @this.dispatch('utj.show')),
 * which is a real DOM/JS event chain invisible to Livewire::test() (which
 * calls component methods directly and never renders real Bootstrap JS).
 *
 * UbahTanggalJurnal::updateTglJurnal() refuses to redate the entry that is
 * LAST on its date (ordered by right(no_jurnal, 6) desc) - a fixture with
 * only one row on the date would always hit that refusal, so this uses two
 * rows on the same date and edits the earlier one (UJI-DUSK-000001, not
 * -000002).
 *
 * Both jurnal rows are dated "today" so they fall inside
 * JurnalPerbaikan::defaultValues()'s default tglAwal/tglAkhir filter (today
 * only) without the test having to drive the date-range filter inputs.
 */
class UbahTanggalJurnalModalTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    private const NIK = '99999906';

    private const PASSWORD = 'uji-password-123';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('detailjurnal')->where('no_jurnal', 'like', 'UJI-DUSK%')->delete();
        $sik->table('jurnal')->where('no_jurnal', 'like', 'UJI-DUSK%')->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI%')->delete();

        DB::table('jurnal_backup')->where('no_jurnal', 'like', 'UJI-DUSK%')->delete();

        parent::tearDown();
    }

    private function jurnal(string $noJurnal, string $tanggal): void
    {
        $sik = DB::connection('mysql_sik');

        $sik->table('jurnal')->insert([
            'no_jurnal'  => $noJurnal, 'no_bukti' => 'BUKTI-'.$noJurnal, 'tgl_jurnal' => $tanggal,
            'jam_jurnal' => '09:00:00', 'jenis' => 'U', 'keterangan' => 'Jurnal '.$noJurnal,
        ]);

        $sik->table('detailjurnal')->insert([
            'no_jurnal' => $noJurnal, 'kd_rek' => 'UJI.1', 'debet' => 100000, 'kredit' => 0,
        ]);
    }

    /**
     * @test
     */
    public function edits_the_journal_date_through_the_real_modal(): void
    {
        $this->petugasWithPermissions([
            'keuangan.jurnal-perbaikan.read',
            'keuangan.jurnal-perbaikan.ubah-tanggal',
        ], self::NIK);
        $this->givePlaintextPassword(self::NIK, self::PASSWORD);

        $hariIni = now()->toDateString();
        $besok = now()->addDay();
        $tanggalBaru = $besok->toDateString();

        // Chrome's <input type="date"> ignores the "-" separators when keys
        // are sent via Selenium and fills its month/day/year segments in
        // that order from raw digits instead - typing the ISO string
        // verbatim silently produces the wrong date.
        $tanggalBaruKetikan = $besok->format('mdY');

        DB::connection('mysql_sik')->table('rekening')->insert([
            'kd_rek' => 'UJI.1', 'nm_rek' => 'Kas Uji', 'tipe' => 'R', 'balance' => 'D', 'level' => '1',
        ]);

        $this->jurnal('UJI-DUSK-000001', $hariIni);
        $this->jurnal('UJI-DUSK-000002', $hariIni);

        $this->browse(function (Browser $browser) use ($tanggalBaruKetikan, $hariIni, $tanggalBaru) {
            $browser->visit('/login')
                ->type('user', self::NIK)
                ->type('pass', self::PASSWORD)
                ->press('Masuk')
                ->waitForLocation('/admin')
                ->visit('/admin/keuangan/jurnal-perbaikan')
                ->waitForText('UJI-DUSK-000001')
                ->click('#edit-UJI-DUSK-000001')
                // The input is in the page from the first paint, so waiting for
                // the element does not wait for prepareJurnal(). Its response
                // writes the old date into the field; typing before it arrives
                // gets overwritten, and the save then "succeeds" with the date
                // unchanged. Wait for the old date to land first.
                ->waitUntil("document.querySelector('#tgl-jurnal-baru').value === '{$hariIni}'")
                ->type('#tgl-jurnal-baru', $tanggalBaruKetikan)
                ->waitUntil("document.querySelector('#tgl-jurnal-baru').value === '{$tanggalBaru}'")
                ->press('Simpan')
                ->waitForText('berhasil diubah');
        });

        $this->assertSame(
            $tanggalBaru,
            DB::connection('mysql_sik')->table('jurnal')
                ->where('no_jurnal', 'UJI-DUSK-000001')
                ->value('tgl_jurnal')
        );
    }
}
