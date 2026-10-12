<?php

namespace Tests\Browser\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Jurnal\PostingJurnal;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * Posting a jurnal from the Jurnal Posting page, end to end, the way a user
 * does it.
 *
 * InputJurnalPostingTest covers what create() writes. What it cannot cover is
 * the part that only exists in the browser: the account for each line is chosen
 * from a Select2 dropdown inside a wire:ignore block, and the only thing carrying
 * that choice back to Livewire is a jQuery change handler calling @this.set().
 * Lines added after the first are initialised by a Livewire.on('detailAdded')
 * listener, which has to find the new <select> already in the DOM. If either
 * link breaks, the form looks filled in and the journal is posted against no
 * account, or not at all. The run ends on the print page create() redirects to,
 * which has to show the journal that was just posted.
 */
class PostingJurnalFlowTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    private const NIK = '99999906';

    private const PASSWORD = 'uji-password-123';

    private const NO_BUKTI = 'BKT-DUSK-001';

    protected function tearDown(): void
    {
        $sik = DB::connection('mysql_sik');

        $noJurnal = $sik->table('jurnal')->where('no_bukti', 'like', 'BKT-DUSK%')->pluck('no_jurnal');

        $sik->table('detailjurnal')->whereIn('no_jurnal', $noJurnal)->delete();
        $sik->table('jurnal')->whereIn('no_jurnal', $noJurnal)->delete();
        DB::connection('mysql_smc')->table('posting_jurnal')->whereIn('no_jurnal', $noJurnal)->delete();
        $sik->table('rekening')->where('kd_rek', 'like', 'UJI.DUSK%')->delete();

        parent::tearDown();
    }

    /**
     * Open the Select2 dropdown for one line and choose an account by typing
     * part of its name, as a user would.
     */
    private function pilihRekening(Browser $browser, int $baris, string $cari): void
    {
        $browser->waitFor("span[aria-labelledby=\"select2-kd_rek_{$baris}-container\"]")
            ->click("span[aria-labelledby=\"select2-kd_rek_{$baris}-container\"]")
            ->waitFor('.select2-container--open .select2-search__field')
            ->keys('.select2-container--open .select2-search__field', $cari, '{enter}')
            ->waitUntilMissing('.select2-container--open');
    }

    /**
     * @test
     */
    public function a_balanced_journal_posts_with_the_accounts_chosen_in_select2(): void
    {
        $this->petugasWithPermissions([
            'keuangan.posting-jurnal.read',
            'keuangan.posting-jurnal.create',
        ], self::NIK);
        $this->givePlaintextPassword(self::NIK, self::PASSWORD);

        DB::connection('mysql_sik')->table('rekening')->insert([
            ['kd_rek' => 'UJI.DUSK.1', 'nm_rek' => 'Kas Dusk', 'tipe' => 'N', 'balance' => 'D', 'level' => '1'],
            ['kd_rek' => 'UJI.DUSK.2', 'nm_rek' => 'Pendapatan Dusk', 'tipe' => 'R', 'balance' => 'K', 'level' => '1'],
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login');
            $browser->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                ->type('user', self::NIK)
                ->type('pass', self::PASSWORD)
                ->press('Masuk')
                ->waitForLocation('/admin')
                ->visit('/admin/keuangan/posting-jurnal')
                ->press('Jurnal Baru')
                ->waitFor('#modal-input-posting-jurnal.show')
                ->type('#no_bukti', self::NO_BUKTI)
                // Penyesuaian rather than the default, so a posting that drops
                // the choice and files it as Umum shows up on the print page.
                ->select('#jenis', 'P')
                ->type('#keterangan', 'Setoran kas dusk');

            $this->pilihRekening($browser, 0, 'Kas Dusk');
            $browser->type('#debet-0', '175000');

            $browser->press('Tambah Detail');

            // The second line's <select> only becomes a Select2 once the
            // detailAdded listener has run against the re-rendered rows.
            $this->pilihRekening($browser, 1, 'Pendapatan Dusk');
            $browser->type('#kredit-1', '175000')
                ->press('Tambah Jurnal')
                ->waitFor('#hapus-0')
                ->press('Simpan')
                ->waitForLocation('/admin/keuangan/cetak-posting-jurnal')
                ->assertSee(self::NO_BUKTI)
                ->assertSee('PENYESUAIAN')
                ->assertDontSee('UMUM')
                ->assertSee('SETORAN KAS DUSK, DIPOSTING OLEH '.self::NIK)
                ->assertSee('UJI.DUSK.1')
                ->assertSee('UJI.DUSK.2');
        });

        $jurnal = Jurnal::query()->where('no_bukti', self::NO_BUKTI)->sole();

        $this->assertSame('P', $jurnal->jenis);

        $this->assertEqualsCanonicalizing(
            [['kd_rek' => 'UJI.DUSK.1', 'debet' => 175000, 'kredit' => 0], ['kd_rek' => 'UJI.DUSK.2', 'debet' => 0, 'kredit' => 175000]],
            DB::connection('mysql_sik')->table('detailjurnal')
                ->where('no_jurnal', $jurnal->no_jurnal)
                ->get(['kd_rek', 'debet', 'kredit'])
                ->map(fn ($r) => ['kd_rek' => $r->kd_rek, 'debet' => (int) $r->debet, 'kredit' => (int) $r->kredit])
                ->all()
        );

        $this->assertTrue(PostingJurnal::query()->where('no_jurnal', $jurnal->no_jurnal)->exists());
    }
}
