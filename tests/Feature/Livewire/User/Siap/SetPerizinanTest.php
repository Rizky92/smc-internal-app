<?php

namespace Tests\Feature\Livewire\User\Siap;

use App\Livewire\Pages\User\Siap\SetPerizinan;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: setting one user's SIAP roles and direct permissions at once.
 *
 * syncRoles()/syncPermissions() both replace the full set rather than add to
 * it, so a role the user already held and isn't in checkedRoles this time is
 * removed - that's the behaviour worth pinning, not just "a role got added".
 */
class SetPerizinanTest extends TestCase
{
    /**
     * @test
     */
    public function refuses_to_save_without_the_superadmin_role(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $target = $this->petugasWithRole('Role Lama Uji', '99999902');

        Livewire::actingAs($petugas)
            ->test(SetPerizinan::class)
            ->set('nrp', '99999902')
            ->set('checkedRoles', [Role::findByName('Role Lama Uji', 'web')->id])
            ->call('save')
            ->assertDispatched('data-denied');

        $this->assertTrue($target->fresh()->hasRole('Role Lama Uji'));
    }

    /**
     * @test
     *
     * syncRoles() replaces the set: Role Lama Uji is not in checkedRoles, so
     * it's removed even though nothing explicitly asked to remove it.
     */
    public function replaces_the_users_roles_and_permissions(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');
        $this->petugasWithRole('Role Lama Uji', '99999902');

        $roleBaru = Role::findOrCreate('Role Baru Uji', 'web');
        $permBaru = Permission::findOrCreate('uji.permission.baru', 'web');

        Livewire::actingAs($petugas)
            ->test(SetPerizinan::class)
            ->set('nrp', '99999902')
            ->set('checkedRoles', [$roleBaru->id])
            ->set('checkedPermissions', [$permBaru->id])
            ->call('save')
            ->assertDispatched('flash.success');

        $target = User::findByNRP('99999902');
        $this->assertTrue($target->hasRole('Role Baru Uji'));
        $this->assertFalse($target->hasRole('Role Lama Uji'));
        $this->assertTrue($target->hasPermissionTo('uji.permission.baru'));
    }

    /**
     * @test
     */
    public function preparing_a_user_fills_the_form(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $role = Role::findOrCreate('Role Uji', 'web');

        Livewire::actingAs($petugas)
            ->test(SetPerizinan::class)
            ->dispatch('siap.prepare-set', nrp: '99999902', nama: 'Petugas Uji', roleIds: [$role->id], permissionIds: [])
            ->assertSet('nrp', '99999902')
            ->assertSet('nama', 'Petugas Uji')
            ->assertSet('checkedRoles', [$role->id]);
    }

    /**
     * @test
     *
     * getOtherPermissionsProperty() only lists permissions not already
     * bundled into a role - a permission attached to a role still renders
     * (nested under that role), so the checklist doesn't show it a second
     * time as a standalone entry under "Perizinan lainnya".
     */
    public function lists_only_permissions_not_already_attached_to_a_role(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $role = Role::findOrCreate('Role Uji', 'web');
        $permissionInRole = Permission::findOrCreate('uji.permission.dalam-role', 'web');
        $role->givePermissionTo($permissionInRole);

        $permissionMandiri = Permission::findOrCreate('uji.permission.mandiri', 'web');

        $lainnya = Livewire::actingAs($petugas)
            ->test(SetPerizinan::class)
            ->get('otherPermissions');

        $this->assertTrue($lainnya->contains('id', $permissionMandiri->id));
        $this->assertFalse($lainnya->contains('id', $permissionInRole->id));
    }
}
