<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\Support\AntreanFarmasiFixture;
use Tests\DuskTestCase;

/**
 * Chromedriver must match the installed Chrome. Dusk 6's dusk:chrome-driver cannot
 * download drivers for Chrome 115+, so fetch the matching win64 chromedriver from
 * https://googlechromelabs.github.io/chrome-for-testing/ and place it at
 * vendor/laravel/dusk/bin/chromedriver-win.exe.
 */
class AntreanFarmasiTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        AntreanFarmasiFixture::clean();
    }

    protected function tearDown(): void
    {
        AntreanFarmasiFixture::clean();

        parent::tearDown();
    }

    public function test_resep_hari_ini_tampil_di_list_sesuai_status()
    {
        $names = AntreanFarmasiFixture::seed(2, 1);

        $this->browse(function (Browser $browser) use ($names) {
            $browser->visit('/antrean-farmasi')
                ->assertSeeIn('#marquee-pengerjaan', $names['pengerjaan'][0])
                ->assertSeeIn('#marquee-pengerjaan', $names['pengerjaan'][1])
                ->assertSeeIn('#marquee-penyerahan', $names['penyerahan'][0])
                ->assertDontSeeIn('#marquee-pengerjaan', $names['penyerahan'][0]);
        });
    }
}
