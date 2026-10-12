<?php

namespace Tests\Feature\Livewire\HakAkses\Siap;

use App\Livewire\Pages\HakAkses\Siap\ModalPerizinan;
use App\Models\Aplikasi\Permission;
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

    private function superadmin()
    {
        return $this->petugasWithRole(config('permission.superadmin_name'), '99999901');
    }

    /**
     * @test
     *
     * Managing roles is a superadmin-only action, checked by role rather than
     * by permission: nothing a role grants can let a user grant it further.
     */
    public function refuses_to_create_a_role_for_anyone_but_a_superadmin(): void
    {
        $izin = Permission::findOrCreate('uji.perizinan.a', 'web');

        Livewire::actingAs($this->petugasWithPermissions([], '99999902'))
            ->test(ModalPerizinan::class)
            ->set('roleName', 'Role Uji Ditolak')
            ->set('checkedPermissions', [$izin->id => $izin->id])
            ->call('create')
            ->assertNotDispatched('role-created');

        $this->assertSame(0, Role::query()->where('name', 'Role Uji Ditolak')->count());
    }

    /**
     * @test
     */
    public function refuses_to_update_a_role_for_anyone_but_a_superadmin(): void
    {
        $role = Role::findOrCreate('Role Uji Lama', 'web');

        Livewire::actingAs($this->petugasWithPermissions([], '99999902'))
            ->test(ModalPerizinan::class)
            ->dispatch('siap.prepare', $role->id)
            ->set('roleName', 'Role Uji Diubah')
            ->call('update')
            ->assertNotDispatched('role-updated');

        $this->assertSame('Role Uji Lama', Role::findById($role->id)->name);
    }

    /**
     * @test
     */
    public function renames_the_selected_role(): void
    {
        $role = Role::findOrCreate('Role Uji Lama', 'web');

        Livewire::actingAs($this->superadmin())
            ->test(ModalPerizinan::class)
            ->dispatch('siap.prepare', $role->id)
            ->set('roleName', 'Role Uji Diubah')
            ->call('update')
            ->assertDispatched('role-updated');

        $this->assertSame('Role Uji Diubah', Role::findById($role->id)->name);
    }

    /**
     * @test
     *
     * prepare() loads the role's permissions as id => id, the shape update()
     * turns back into a list. Unticking one in the browser replaces its value
     * with false, which drops it from that list — so revoking works.
     */
    public function unticking_a_permission_revokes_it_from_the_role(): void
    {
        $a = Permission::findOrCreate('uji.perizinan.a', 'web');
        $b = Permission::findOrCreate('uji.perizinan.b', 'web');
        $role = Role::findOrCreate('Role Uji Lama', 'web');
        $role->givePermissionTo([$a, $b]);

        Livewire::actingAs($this->superadmin())
            ->test(ModalPerizinan::class)
            ->dispatch('siap.prepare', $role->id)
            ->assertSet('checkedPermissions', [$a->id => $a->id, $b->id => $b->id])
            ->set("checkedPermissions.{$a->id}", false)
            ->call('update')
            ->assertDispatched('role-updated');

        $this->assertSame(['uji.perizinan.b'], Role::findById($role->id)->permissions()->pluck('name')->all());
    }

    /**
     * Each checkbox is bound as wire:model="checkedPermissions.{id}". That path
     * is not an array, so Livewire 3 sends the box's checked state — true —
     * rather than its value attribute, as Livewire 2 did. update() used to hand
     * the map's values to syncPermissions(), which cannot resolve true to a
     * permission and skipped it without complaint: the save reported success
     * and a permission could be taken away from a role but never given to one.
     *
     * The ids are now read from the keys of the ticked entries.
     *
     * @test
     */
    public function ticking_a_permission_grants_it(): void
    {
        $a = Permission::findOrCreate('uji.perizinan.a', 'web');
        $b = Permission::findOrCreate('uji.perizinan.b', 'web');
        $role = Role::findOrCreate('Role Uji Lama', 'web');
        $role->givePermissionTo($a);

        Livewire::actingAs($this->superadmin())
            ->test(ModalPerizinan::class)
            ->dispatch('siap.prepare', $role->id)
            ->set("checkedPermissions.{$b->id}", true)
            ->call('update')
            ->assertDispatched('role-updated');

        $this->assertSame(
            ['uji.perizinan.a', 'uji.perizinan.b'],
            Role::findById($role->id)->permissions()->orderBy('name')->pluck('name')->all()
        );
    }

    /**
     * The same binding, seen from "Role Baru": every box ticked on a new role
     * arrives as true. The role used to be created, announced as created, and
     * hold no permissions at all.
     *
     * @test
     */
    public function a_new_role_gets_the_permissions_ticked_for_it(): void
    {
        $a = Permission::findOrCreate('uji.perizinan.a', 'web');
        $b = Permission::findOrCreate('uji.perizinan.b', 'web');
        $c = Permission::findOrCreate('uji.perizinan.c', 'web');

        Livewire::actingAs($this->superadmin())
            ->test(ModalPerizinan::class)
            ->dispatch('siap.prepare')
            ->set('roleName', 'Role Uji Baru')
            ->set("checkedPermissions.{$a->id}", true)
            ->set("checkedPermissions.{$b->id}", true)
            // Ticked and then unticked again before saving.
            ->set("checkedPermissions.{$c->id}", true)
            ->set("checkedPermissions.{$c->id}", false)
            ->call('create')
            ->assertDispatched('role-created');

        $role = Role::query()->where('name', 'Role Uji Baru')->sole();

        $this->assertSame(
            ['uji.perizinan.a', 'uji.perizinan.b'],
            $role->permissions()->orderBy('name')->pluck('name')->all()
        );
    }

    /**
     * A permission untouched in the form keeps the id => id shape prepare()
     * loaded it with. Saving without changing anything must leave the role
     * exactly as it was.
     *
     * @test
     */
    public function saving_without_touching_a_box_keeps_the_role_as_it_was(): void
    {
        $a = Permission::findOrCreate('uji.perizinan.a', 'web');
        $b = Permission::findOrCreate('uji.perizinan.b', 'web');
        $role = Role::findOrCreate('Role Uji Lama', 'web');
        $role->givePermissionTo([$a, $b]);

        Livewire::actingAs($this->superadmin())
            ->test(ModalPerizinan::class)
            ->dispatch('siap.prepare', $role->id)
            ->call('update')
            ->assertDispatched('role-updated');

        $this->assertSame(
            ['uji.perizinan.a', 'uji.perizinan.b'],
            Role::findById($role->id)->permissions()->orderBy('name')->pluck('name')->all()
        );
    }
}
