<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Informasi\JadwalDokter was registered twice: once inside the admin group and
 * once at a bare public URL.
 *
 * The public one could not work in either state. It renders through BaseLayout,
 * whose constructor calls user(), so a guest got AuthenticationException and was
 * redirected to login. Logging in did not help: the bare route carried no name,
 * and the admin layout's navbar calls Breadcrumbs::render(), which selects by
 * the current route's name and throws "The current route is not named" without
 * one. So it was a redirect to login for guests and a 500 for everybody else.
 *
 * The public-display counterpart already exists separately as
 * Informasi\DisplayJadwalDokter at /display-jadwal-dokter, which renders through
 * CustomerLayout and needs no user.
 */
class JadwalDokterRoutingTest extends TestCase
{
    /**
     * @test
     *
     * Guards against the bare route being reintroduced: it cannot work, because
     * the page it serves needs both an authenticated user and a route name.
     */
    public function is_not_served_publicly(): void
    {
        $this->get('/jadwal-dokter')->assertNotFound();
    }

    /**
     * @test
     *
     * The registration that does work. It carries no can: middleware — unlike
     * every other route in its group — so RouteSweepTest, which only covers
     * permission-gated routes, does not reach it.
     */
    public function opens_for_an_authenticated_petugas(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $this->withoutExceptionHandling();

        $this->actingAs($petugas)
            ->get('/admin/informasi/jadwal-dokter')
            ->assertOk();
    }

    /**
     * @test
     */
    public function sends_a_guest_to_login(): void
    {
        $this->get('/admin/informasi/jadwal-dokter')->assertRedirect('/login');
    }
}
