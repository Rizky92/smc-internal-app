<?php

namespace Tests\Feature\Livewire\HakAkses;

use App\Livewire\Pages\HakAkses\Siap;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the SIAP role list.
 *
 * A pure read-only list - the actual create/edit write path is already
 * covered by ModalPerizinanTest. This suite just pins that a role's
 * permissions are eager-loaded and rendered, since with('permissions')
 * dropping silently would still let the page render, just without a
 * visible reason why a role's permission count looks wrong.
 */
class SiapTest extends TestCase
{
    /**
     * @test
     */
    public function lists_a_role_with_its_permissions(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $role = Role::findOrCreate('Role Uji', 'web');
        $role->givePermissionTo(Permission::findOrCreate('uji.permission.satu', 'web'));

        Livewire::actingAs($petugas)
            ->test(Siap::class)
            ->assertSee('Role Uji')
            ->assertSee('uji.permission.satu');
    }
}
