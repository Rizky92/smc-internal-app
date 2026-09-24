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
                ->assertDontSeeIn('#marquee-pengerjaan', $names['penyerahan'][0])
                ->assertDontSeeIn('#marquee-pengerjaan', 'Dokter Peresep');
        });
    }

    public function test_panggilan_baru_tampil_dalam_15_detik_dan_disorot()
    {
        AntreanFarmasiFixture::panggil('0101', now()->subMinutes(5)->format('H:i:s'));

        $this->browse(function (Browser $browser) {
            $browser->visit('/antrean-farmasi')
                ->assertSeeIn('#nomor-dipanggil', '0101')
                ->assertScript("!document.querySelector('#nomor-dipanggil').classList.contains('is-new')");

            AntreanFarmasiFixture::panggil('0102');

            // The card polls every 10s, so a new call must show within 15s and be highlighted.
            $browser->waitForTextIn('#nomor-dipanggil', '0102', 15)
                ->assertScript("document.querySelector('#nomor-dipanggil').classList.contains('is-new')");
        });
    }

    public function test_display_muat_satu_layar_di_1080p()
    {
        AntreanFarmasiFixture::seed(12, 12);

        $this->browse(function (Browser $browser) {
            $this->viewport($browser, 1920, 1080)
                ->visit('/antrean-farmasi')
                ->pause(500)
                ->assertScript('document.documentElement.scrollHeight <= window.innerHeight')
                ->assertScript("document.querySelector('#nomor-dipanggil').getBoundingClientRect().bottom <= window.innerHeight");
        });
    }

    public function test_list_yang_melebihi_tinggi_kotak_bergulir()
    {
        // 9 rows: more than 60vh holds at 1080p, but not more than the old 10-row threshold.
        AntreanFarmasiFixture::seed(0, 9);

        $this->browse(function (Browser $browser) {
            $browser->visit('/antrean-farmasi')
                ->waitUsing(5, 100, fn () => $this->bergulir($browser, 'penyerahan'))
                ->assertScript("document.querySelector('#marquee-pengerjaan .js-marquee-wrapper') === null");
        });
    }

    public function test_list_dengan_tepat_sepuluh_baris_bergulir_lalu_refresh()
    {
        AntreanFarmasiFixture::seed(10);

        $this->browse(function (Browser $browser) {
            $browser->visit('/antrean-farmasi')
                ->waitUsing(5, 100, fn () => $this->bergulir($browser, 'pengerjaan'));

            $baru = AntreanFarmasiFixture::seed(1)['pengerjaan'][0];

            // One marquee pass must end in a refresh that shows the new row. jquery.marquee
            // scales the pass with content height: 10 rows take about 67s.
            $browser->waitUsing(120, 500, fn () => $this->berisi($browser, 'pengerjaan', $baru));

            $this->assertTrue($this->berisi($browser, 'pengerjaan', $baru));
        });
    }

    public function test_fallback_refresh_tidak_memotong_putaran_yang_berjalan()
    {
        AntreanFarmasiFixture::seed(10);

        $this->browse(function (Browser $browser) {
            // A 5s interval is far shorter than a 10-row pass, so a fixed fallback would cut it.
            $browser->visit('/antrean-farmasi?refresh=5')
                ->waitUsing(5, 100, fn () => $this->bergulir($browser, 'pengerjaan'))
                ->script("window.__t0 = Date.now(); window.__refreshes = []; Livewire.hook('message.processed', (m, c) => { if (c.fingerprint.name.endsWith('list-pengerjaan')) window.__refreshes.push(Date.now() - window.__t0); });");

            $pass = $browser->script("const s = getComputedStyle(document.querySelector('#marquee-pengerjaan .js-marquee-wrapper')); return (parseFloat(s.animationDuration) + parseFloat(s.animationDelay)) * 1000;")[0];

            $browser->waitUsing(150, 500, fn () => $browser->script('return window.__refreshes.length > 0')[0]);

            $this->assertGreaterThanOrEqual($pass - 2000, $browser->script('return window.__refreshes[0]')[0]);
        });
    }

    public function test_list_yang_tidak_bergulir_refresh_sendiri()
    {
        AntreanFarmasiFixture::seed(3);

        $this->browse(function (Browser $browser) {
            $browser->visit('/antrean-farmasi?refresh=2')
                ->pause(500)
                ->assertScript("document.querySelector('#marquee-pengerjaan .js-marquee-wrapper') === null")
                ->script("window.__refreshes = []; Livewire.hook('message.processed', (m, c) => { if (c.fingerprint.name.endsWith('list-pengerjaan')) window.__refreshes.push(Date.now()); });");

            $baru = AntreanFarmasiFixture::seed(1)['pengerjaan'][0];

            $browser->waitUsing(10, 200, fn () => $this->berisi($browser, 'pengerjaan', $baru))
                ->pause(1000);

            // One timer tick must be one round trip, not a refresh that triggers a second one.
            $this->assertTrue($browser->script('return window.__refreshes.every((t, i, a) => i === 0 || t - a[i - 1] > 1000)')[0]);
        });
    }

    public function test_layar_lebih_kecil_tetap_muat_dan_bergulir_saat_overflow()
    {
        // The layout is sized in vh, so a list holds ~8 rows at any 16:9 viewport.
        AntreanFarmasiFixture::seed(9);

        $this->browse(function (Browser $browser) {
            $this->viewport($browser, 1366, 768)
                ->visit('/antrean-farmasi')
                ->waitUsing(5, 100, fn () => $this->bergulir($browser, 'pengerjaan'))
                ->assertScript('document.documentElement.scrollHeight <= window.innerHeight')
                ->assertScript("document.querySelector('#nomor-dipanggil').getBoundingClientRect().bottom <= window.innerHeight");

            $this->assertTrue($this->bergulir($browser, 'pengerjaan'));
        });
    }

    /**
     * Size the viewport, not the window: the TV runs full screen, while resize() includes
     * the browser frame (a 1920x1080 window only leaves a ~929px tall viewport).
     */
    private function viewport(Browser $browser, int $width, int $height): Browser
    {
        $browser->resize($width, $height);
        $frame = $browser->script('return window.outerHeight - window.innerHeight')[0];

        return $browser->resize($width, $height + $frame);
    }

    private function berisi(Browser $browser, string $list, string $teks): bool
    {
        // textContent, not visible text: rows below the fold are clipped by the box.
        return $browser->script("return document.querySelector('#marquee-{$list}').textContent.includes(".json_encode($teks).')')[0];
    }

    private function bergulir(Browser $browser, string $list): bool
    {
        return $browser->script("return document.querySelector('#marquee-{$list} .js-marquee-wrapper') !== null")[0];
    }
}
