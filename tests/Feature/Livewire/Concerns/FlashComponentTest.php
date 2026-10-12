<?php

namespace Tests\Feature\Livewire\Concerns;

use Livewire\Livewire;
use Tests\Fixtures\Livewire\ReportHarness;
use Tests\TestCase;

/**
 * FlashComponent, inherited by 92 of the 116 page components — the widest reach
 * of any trait in app/Livewire/Concerns.
 *
 * It is how every success and every refusal in the application reaches the user.
 * Each helper writes three session keys, and <x-flash /> turns them into a
 * Bootstrap alert on the next render: the message, the contextual class that
 * colours it, and the icon. Asserting the rendered banner rather than the
 * session keys is deliberate — the keys are flashed, so they are gone by the
 * time the test process could read them, and the banner is what the user gets
 * either way. Getting type or icon wrong makes a refusal look like a
 * confirmation, so all three are pinned.
 */
class FlashComponentTest extends TestCase
{
    private function harness()
    {
        return Livewire::actingAs($this->petugasWithPermissions())
            ->test(ReportHarness::class);
    }

    /**
     * @test
     */
    public function flash_success_tampil_hijau_dengan_ikon_centang(): void
    {
        $this->harness()
            ->dispatch('flash.success', 'Data tersimpan')
            ->assertSee('Data tersimpan')
            ->assertSee('alert-success', false)
            ->assertSee('fa-check-circle', false);
    }

    /**
     * @test
     */
    public function flash_error_tampil_merah_dengan_ikon_silang(): void
    {
        $this->harness()
            ->dispatch('flash.error', 'Tidak diizinkan')
            ->assertSee('Tidak diizinkan')
            ->assertSee('alert-danger', false)
            ->assertSee('fa-times-circle', false);
    }

    /**
     * @test
     */
    public function flash_warning_tampil_kuning_dengan_ikon_seru(): void
    {
        $this->harness()
            ->dispatch('flash.warning', 'Periksa kembali')
            ->assertSee('Periksa kembali')
            ->assertSee('alert-warning', false)
            ->assertSee('fa-exclamation-triangle', false);
    }

    /**
     * flash.info is the odd one: its type is "dark", not "info", so it renders
     * as alert-dark. ExcelExportable fires it at the start of every Synchronous
     * Export, which makes it the banner users see most often.
     *
     * @test
     */
    public function flash_info_tampil_sebagai_alert_dark_bukan_alert_info(): void
    {
        $this->harness()
            ->dispatch('flash.info', 'Proses ekspor dimulai')
            ->assertSee('Proses ekspor dimulai')
            ->assertSee('alert-dark', false)
            ->assertDontSee('alert-info', false)
            ->assertSee('fa-info-circle', false);
    }

    /**
     * @test
     */
    public function pesan_bawaan_dipakai_saat_event_dikirim_tanpa_payload(): void
    {
        $this->harness()
            ->dispatch('flash.success')
            ->assertSee('Sukses melakukan perubahan data');
    }

    /**
     * @test
     */
    public function pesan_bawaan_penolakan_berbunyi_sebagai_penolakan(): void
    {
        $this->harness()
            ->dispatch('flash.error')
            ->assertSee('Anda tidak diizinkan untuk melakukan aksi ini!')
            ->assertSee('alert-danger', false);
    }

    /**
     * The raw entry point the four helpers funnel into, reachable on its own for
     * a component that wants to write keys the helpers do not cover.
     *
     * @test
     */
    public function flash_mentah_menulis_setiap_pasangan_yang_diberikan(): void
    {
        $this->harness()
            ->dispatch('flash', [
                'flash.message' => 'Pesan khusus',
                'flash.type'    => 'primary',
                'flash.icon'    => 'bell',
            ])
            ->assertSee('Pesan khusus')
            ->assertSee('alert-primary', false)
            ->assertSee('fa-bell', false);
    }

    /**
     * <x-flash /> renders nothing at all until something is flashed, so a page
     * does not open with an empty alert box.
     *
     * @test
     */
    public function tanpa_flash_tidak_ada_banner_yang_dirender(): void
    {
        $this->harness()->assertDontSee('alert-dismissable', false);
    }
}
