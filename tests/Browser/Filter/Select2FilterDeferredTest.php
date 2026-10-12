<?php

namespace Tests\Browser\Filter;

use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Concerns\LogsInWithRealPassword;
use Tests\DuskTestCase;

/**
 * A choice in <x-filter.select2> is applied when the user searches, not the
 * moment it is made.
 *
 * Report queries against Khanza are heavy. The component copies the choice
 * into Livewire with @this.set(); in Livewire 2 its third argument true meant
 * "defer", in Livewire 3 it means "live", so every choice ran the report
 * straight away. Hasil MCU Karyawan is used because its Instansi filter needs
 * nothing but one perusahaan_pasien row to have an option to choose.
 */
class Select2FilterDeferredTest extends DuskTestCase
{
    use LogsInWithRealPassword;

    private const NIK = '99999908';

    private const PASSWORD = 'uji-password-123';

    protected function tearDown(): void
    {
        DB::connection('mysql_sik')->table('perusahaan_pasien')->where('kode_perusahaan', 'like', 'UJI%')->delete();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function a_choice_is_applied_only_when_searching(): void
    {
        DB::connection('mysql_sik')->table('perusahaan_pasien')->insert([
            'kode_perusahaan' => 'UJIPT1', 'nama_perusahaan' => 'PT Uji Filter',
        ]);

        $this->petugasWithPermissions(['lab.hasil-mcu-karyawan.read'], self::NIK);
        $this->givePlaintextPassword(self::NIK, self::PASSWORD);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('user', self::NIK)
                ->type('pass', self::PASSWORD)
                ->press('Masuk')
                ->waitForLocation('/admin')
                ->visit('/admin/laboratorium/hasil-mcu-karyawan')
                ->waitFor('span[aria-labelledby="select2-perusahaan-container"]')
                // Let wire:init's loadProperties request finish before counting.
                ->pause(1500)
                ->script("window.__permintaan = 0; Livewire.hook('request', () => { window.__permintaan++ })");

            $browser->click('span[aria-labelledby="select2-perusahaan-container"]')
                ->waitFor('.select2-container--open .select2-search__field')
                ->keys('.select2-container--open .select2-search__field', 'PT Uji Filter', '{enter}')
                ->waitUntilMissing('.select2-container--open')
                ->pause(1500)
                ->assertScript('window.__permintaan', 0)
                ->assertQueryStringMissing('perusahaan')
                // Select2 renders its own search inputs too; this is the page's.
                ->keys('input.form-control[type="search"][wire\\:model="cari"]', '{enter}')
                ->waitUntil('window.__permintaan >= 1')
                ->waitUsing(5, 100, fn () => str_contains($browser->driver->getCurrentURL(), 'perusahaan=UJIPT1'));
        });
    }
}
