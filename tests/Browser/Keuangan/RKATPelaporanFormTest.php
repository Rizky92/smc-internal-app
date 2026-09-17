<?php

namespace Tests\Browser\Keuangan;

use App\Models\Bidang;
use App\Models\Keuangan\RKAT\Anggaran;
use App\Models\Keuangan\RKAT\AnggaranBidang;
use App\Models\Keuangan\RKAT\PemakaianAnggaran;
use App\Settings\RKATSettings;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * Filling in a spending report through the real form, with the budget chosen in
 * its Select2 dropdown.
 *
 * The dropdown is <x-form.select2>, which wraps a wire:ignore'd <select> and
 * copies each choice into Livewire with @this.set(). The modal's component
 * dispatches select2.hydrate on every request, and the component re-initialises
 * Select2 whenever it hears it. Under Livewire 3 that combination wiped every
 * choice: set()'s third argument went from "defer" to "live", so choosing a
 * budget started a request, the request re-ran the initialiser, and the
 * initialiser put the placeholder back and wrote it into the model. No report
 * could be created or edited — validation always refused the budget.
 *
 * RKATInputPelaporanTest sets anggaranBidangId directly and cannot see any of
 * this. Both paths a user takes are covered: a new report, where "Tambah
 * Detail" adds a second round trip after the choice, and an existing one, where
 * the dropdown has to show the report's own budget and keep it on save.
 */
class RKATPelaporanFormTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    private const NIK = '99999905';

    private const PASSWORD = 'uji-password-123';

    protected function tearDown(): void
    {
        $smc = DB::connection('mysql_smc');

        // See RKATInputPenetapanTest::tearDown() for why the checks come off.
        $smc->statement('set foreign_key_checks = 0');
        foreach (['pemakaian_anggaran_detail', 'pemakaian_anggaran', 'anggaran_bidang', 'anggaran', 'bidang'] as $table) {
            $smc->table($table)->delete();
        }
        $smc->statement('set foreign_key_checks = 1');

        parent::tearDown();
    }

    private function anggaranBidang(string $bidang, string $kategori): AnggaranBidang
    {
        return AnggaranBidang::create([
            'anggaran_id'      => Anggaran::create(['nama' => $kategori])->id,
            'bidang_id'        => Bidang::create(['nama' => $bidang])->id,
            'tahun'            => app(RKATSettings::class)->tahun,
            'nominal_anggaran' => 10000000,
        ]);
    }

    private function masuk(Browser $browser): Browser
    {
        $this->petugasWithPermissions([
            'keuangan.rkat-pelaporan.read',
            'keuangan.rkat-pelaporan.create',
            'keuangan.rkat-pelaporan.update',
        ], self::NIK);
        $this->givePlaintextPassword(self::NIK, self::PASSWORD);

        $browser->visit('/login');
        $browser->driver->manage()->deleteAllCookies();

        return $browser->visit('/login')
            ->type('user', self::NIK)
            ->type('pass', self::PASSWORD)
            ->press('Masuk')
            ->waitForLocation('/admin')
            ->visit('/admin/keuangan/rkat-pelaporan');
    }

    private function nilaiSelect2(): string
    {
        return "document.querySelector('#select2-anggaran-bidang-id-container').textContent.trim()";
    }

    private function baris(int $nomor, string $kolom): string
    {
        return "#detail-pemakaian li:nth-child({$nomor}) input[wire\\:model\$=\".{$kolom}\"]";
    }

    /**
     * Count every select2.hydrate the page hears.
     *
     * Each one rebuilds the Select2 widget. Clicking it while that happens hits
     * an element that is about to be replaced: the dropdown never opens, or the
     * reference goes stale. Waiting on this count rather than on a fixed pause
     * is what lets the test act only once the widget has settled.
     */
    private function hitungInisialisasiSelect2(Browser $browser): void
    {
        $browser->script("window.__select2Hydrate = 0; Livewire.on('select2.hydrate', () => { window.__select2Hydrate++ })");
    }

    private function tungguInisialisasiSelect2(Browser $browser, int $keBerapa): Browser
    {
        return $browser->waitUntil("window.__select2Hydrate >= {$keBerapa}")->pause(300);
    }

    /**
     * @test
     */
    public function a_new_report_keeps_the_budget_chosen_in_the_dropdown(): void
    {
        $pilihan = $this->anggaranBidang('Bidang Dusk B', 'Kategori Dusk B');
        $this->anggaranBidang('Bidang Dusk A', 'Kategori Dusk A');

        $tahun = app(RKATSettings::class)->tahun;
        $label = "Bidang Dusk B - {$tahun} - Kategori Dusk B";

        $this->browse(function (Browser $browser) use ($label) {
            $this->masuk($browser);
            $this->hitungInisialisasiSelect2($browser);

            $browser->press('Laporan Baru')
                ->waitFor('#modal-input-pelaporan-rkat.show');

            // Opening via "Laporan Baru" dispatches prepare, and its response
            // rebuilds the widget once.
            $this->tungguInisialisasiSelect2($browser, 1)
                ->click('span[aria-labelledby="select2-anggaran-bidang-id-container"]')
                ->waitFor('.select2-container--open .select2-search__field')
                ->keys('.select2-container--open .select2-search__field', 'Bidang Dusk B', '{enter}')
                ->waitUntilMissing('.select2-container--open')
                // Before the fix the choice itself started a round trip, and
                // its response put the placeholder back. Give such a round
                // trip time to land.
                ->pause(1500)
                ->assertScript($this->nilaiSelect2(), $label)
                ->type('#keterangan', 'Laporan Dusk Baru')
                ->type($this->baris(1, 'keterangan'), 'Kertas A4')
                ->type($this->baris(1, 'nominal'), '150000')
                ->press('Tambah Detail')
                ->waitFor('#detail-pemakaian li:nth-child(2)');

            // Adding a line is a round trip too; check after its rebuild.
            $this->tungguInisialisasiSelect2($browser, 2)
                ->assertScript($this->nilaiSelect2(), $label)
                ->type($this->baris(2, 'keterangan'), 'Tinta printer')
                ->type($this->baris(2, 'nominal'), '50000')
                ->click('#simpandata')
                ->waitForText('berhasil disimpan');
        });

        $laporan = PemakaianAnggaran::query()->where('judul', 'Laporan Dusk Baru')->sole();

        $this->assertSame((int) $pilihan->id, (int) $laporan->anggaran_bidang_id);
        $this->assertSame(2, $laporan->detail()->count());
        $this->assertEquals(200000, $laporan->detail()->sum('nominal'));
    }

    /**
     * @test
     */
    public function editing_a_report_shows_and_keeps_its_own_budget(): void
    {
        $milikLaporan = $this->anggaranBidang('Bidang Dusk B', 'Kategori Dusk B');
        $this->anggaranBidang('Bidang Dusk A', 'Kategori Dusk A');

        $laporan = PemakaianAnggaran::create([
            'judul'              => 'Laporan Dusk Lama',
            'tgl_dipakai'        => now()->toDateString(),
            'anggaran_bidang_id' => $milikLaporan->id,
            'user_id'            => self::NIK,
        ]);
        $laporan->detail()->createMany([['keterangan' => 'Kertas A4', 'nominal' => 150000]]);

        $tahun = app(RKATSettings::class)->tahun;
        $label = "Bidang Dusk B - {$tahun} - Kategori Dusk B";

        $this->browse(function (Browser $browser) use ($laporan, $label) {
            $this->masuk($browser)
                ->waitForText('Laporan Dusk Lama')
                ->click("button[data-pemakaian-anggaran-id=\"{$laporan->id}\"]")
                ->waitFor('#modal-input-pelaporan-rkat.show')
                ->waitUntil("document.querySelector('#keterangan').value === 'Laporan Dusk Lama'")
                ->pause(1500)
                ->assertScript($this->nilaiSelect2(), $label)
                ->clear('#keterangan')
                ->type('#keterangan', 'Laporan Dusk Diubah')
                ->click('#simpandata')
                ->waitForText('berhasil diupdate');
        });

        $laporan->refresh();

        $this->assertSame('Laporan Dusk Diubah', $laporan->judul);
        $this->assertSame((int) $milikLaporan->id, (int) $laporan->anggaran_bidang_id);
    }
}
