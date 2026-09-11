<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Pages\HakAkses\Siap\ModalPerizinan;
use App\Models\Aplikasi\Role;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: the modal that creates and edits a SIAP role's permission set.
 *
 * "Role Baru" only has a bare data-toggle="modal" button behind it, with no
 * action to reset the form - unlike a row click, which reaches prepare() via
 * JS. Reopening the modal for a new role right after editing one is the only
 * way this component's create/update split is actually exercised end to end.
 */
class ModalPerizinanTest extends TestCase
{
    protected function tearDown(): void
    {
        DB::connection('mysql_smc')->table('roles')->where('name', 'like', 'Role Uji%')->delete();

        parent::tearDown();
    }

    /**
     * @test
     *
     * siap.blade.php's "Role Baru" button dispatches siap.prepare with no id
     * at all (see modal-perizinan.blade.php's shown.bs.modal handler).
     * Previously prepare() only ever reset roleId - roleName and
     * checkedPermissions were left at whatever the last edit set them to.
     */
    public function reopening_for_a_new_role_resets_the_form(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');
        $existing = Role::findOrCreate('Role Uji Lama', 'web');

        Livewire::actingAs($petugas)
            ->test(ModalPerizinan::class)
            ->dispatch('siap.prepare', $existing->id)
            ->assertSet('roleId', $existing->id)
            ->assertSet('roleName', 'Role Uji Lama')
            ->dispatch('siap.prepare')
            ->assertSet('roleId', -1)
            ->assertSet('roleName', '')
            ->assertSet('checkedPermissions', []);
    }

    /**
     * @test
     *
     * roleId itself was always reset unconditionally (the line ran before the
     * old code's `if ($id !== -1)` check), so wire:submit.prevent="{{ $roleId
     * !== -1 ? 'update' : 'create' }}" already routed back to create()
     * correctly even before the fix above - it's roleName/checkedPermissions
     * leaking through that turns "create a new role" into "create a
     * duplicate of the one I was just editing". Pinning the routing anyway,
     * since a future change coupling it to something else should not silently
     * reintroduce the update() misroute this bug class hits everywhere else.
     */
    public function reopening_for_a_new_role_still_submits_to_create(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');
        $existing = Role::findOrCreate('Role Uji Lama', 'web');

        Livewire::actingAs($petugas)
            ->test(ModalPerizinan::class)
            ->dispatch('siap.prepare', $existing->id)
            ->assertSeeHtml('wire:submit.prevent="update"')
            ->dispatch('siap.prepare')
            ->assertSeeHtml('wire:submit.prevent="create"')
            ->assertDontSeeHtml('wire:submit.prevent="update"');
    }
}
