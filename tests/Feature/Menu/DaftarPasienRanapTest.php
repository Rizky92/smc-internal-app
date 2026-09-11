<?php

namespace Tests\Feature\Menu;

use App\Livewire\Pages\Perawatan\DaftarPasienRanap;
use Tests\TestCase;

/**
 * Seam A: route -> rendered page.
 *
 * Asserts only what a browser could observe: the status code, and that the page
 * carries the Livewire component it is supposed to. Nothing here reaches into
 * component internals, so the test survives a Livewire major version change and
 * fails only if the page genuinely stops serving.
 */
class DaftarPasienRanapTest extends TestCase
{
    private const URI = '/admin/perawatan/daftar-pasien-ranap';

    private const PERMISSION = 'perawatan.daftar-pasien-ranap.read';

    /**
     * @test
     */
    public function petugas_with_permission_can_open_the_page(): void
    {
        $petugas = $this->petugasWithPermissions([self::PERMISSION]);

        // A page that breaks during an upgrade should say why, rather than
        // reporting only that 500 is not 200.
        $this->withoutExceptionHandling();

        $this->actingAs($petugas)
            ->get(self::URI)
            ->assertOk()
            ->assertSeeLivewire(DaftarPasienRanap::class);
    }

    /**
     * @test
     */
    public function petugas_without_permission_is_refused(): void
    {
        $petugas = $this->petugasWithPermissions([]);

        // 404, not 403, and deliberately so: Handler::render() rewrites every
        // AuthorizationException into a NotFoundHttpException, so a petugas
        // cannot learn that a page exists by being refused it.
        $this->actingAs($petugas)
            ->get(self::URI)
            ->assertNotFound();
    }

    /**
     * @test
     */
    public function guest_is_sent_to_login(): void
    {
        $this->get(self::URI)->assertRedirect('/login');
    }
}
