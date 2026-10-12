<?php

namespace Tests\Feature\Scopes;

use App\Models\Aplikasi\User;
use Tests\TestCase;

/**
 * Seam D: the cross-connection scope.
 *
 * Identity lives in mysql_sik and authorisation in mysql_smc, with no foreign key
 * between them, so this scope cannot use whereHas. It hand-builds correlated
 * `exists (...)` subqueries against the other connection's schema by name. That
 * raw SQL is exactly what a framework upgrade can quietly change underneath —
 * the grammar, the table qualification, the binding order — so it is worth
 * pinning down.
 *
 * Authorisation reaches a petugas by two independent routes, model_has_roles and
 * model_has_permissions, and the scope has to honour both.
 */
class TampilkanYangMemilikiHakAksesTest extends TestCase
{
    /**
     * @test
     */
    public function includes_a_petugas_holding_a_role(): void
    {
        $berhak = $this->petugasWithRole('perawat', '99999901');

        $this->assertContains(
            $berhak->nik,
            User::query()->tampilkanYangMemilikiHakAkses(true)->pluck('nik')->all()
        );
    }

    /**
     * @test
     */
    public function includes_a_petugas_holding_a_direct_permission(): void
    {
        $berhak = $this->petugasWithPermissions(['perawatan.daftar-pasien-ranap.read'], '99999902');

        $this->assertContains(
            $berhak->nik,
            User::query()->tampilkanYangMemilikiHakAkses(true)->pluck('nik')->all()
        );
    }

    /**
     * @test
     */
    public function excludes_a_petugas_holding_neither(): void
    {
        $tanpaHak = $this->petugasWithPermissions([], '99999903');

        $this->assertNotContains(
            $tanpaHak->nik,
            User::query()->tampilkanYangMemilikiHakAkses(true)->pluck('nik')->all()
        );
    }

    /**
     * @test
     *
     * The combination ManajemenUser actually uses:
     * ->tampilkanYangMemilikiHakAkses($flag)->search($cari). Narrowing by access
     * and then searching must satisfy both conditions, not either one.
     */
    public function combining_with_search_narrows_by_both(): void
    {
        $this->petugasWithRole('perawat', '99999901', 'Budi Santoso');
        $this->petugasWithRole('perawat', '99999902', 'Siti Aminah');

        $hasil = User::query()
            ->tampilkanYangMemilikiHakAkses(true)
            ->search('Budi')
            ->pluck('nama')
            ->all();

        $this->assertContains('Budi Santoso', $hasil);
        $this->assertNotContains('Siti Aminah', $hasil);
    }

    /**
     * @test
     */
    public function passing_false_filters_nobody_out(): void
    {
        $berhak = $this->petugasWithRole('perawat', '99999901');
        $tanpaHak = $this->petugasWithPermissions([], '99999903');

        $hasil = User::query()->tampilkanYangMemilikiHakAkses(false)->pluck('nik')->all();

        $this->assertContains($berhak->nik, $hasil);
        $this->assertContains($tanpaHak->nik, $hasil);
    }
}
