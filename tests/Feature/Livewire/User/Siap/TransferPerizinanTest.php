<?php

namespace Tests\Feature\Livewire\User\Siap;

use App\Livewire\Pages\User\Siap\TransferPerizinan;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Seam B: copying one user's SIAP roles and permissions onto others.
 *
 * Unlike SetPerizinan (which takes the target set from whatever the caller
 * dispatches), prepareTransfer() here reads the SOURCE user's current roles
 * and permissions straight from the database - so the fixture has to give
 * the source something real to read before the transfer can be checked.
 * syncRoles()/syncPermissions() then replace each target's own set, exactly
 * as in SetPerizinan.
 */
class TransferPerizinanTest extends TestCase
{
    /**
     * @return array<string, bool>
     */
    private function checked(string ...$niks): array
    {
        return collect($niks)->mapWithKeys(fn (string $nik): array => [$nik => true])->all();
    }

    /**
     * @test
     */
    public function preparing_a_transfer_loads_the_sources_current_roles_and_permissions(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $sumber = $this->petugasWithRole('Role Sumber Uji', '99999902');
        $permission = Permission::findOrCreate('uji.permission.mandiri', 'web');
        $sumber->givePermissionTo($permission);

        $role = Role::findByName('Role Sumber Uji', 'web');

        Livewire::actingAs($petugas)
            ->test(TransferPerizinan::class)
            ->dispatch('siap.prepare-transfer', nrp: '99999902', nama: 'Sumber Uji')
            ->assertSet('nrp', '99999902')
            ->assertSet('roles', [$role->id => $role->name])
            ->assertSet('permissions', [$permission->id => $permission->name]);
    }

    /**
     * @test
     *
     * A stale nrp - the row disappearing while the table was left open, for
     * instance - must not fatal on a null model.
     */
    public function throws_when_preparing_a_transfer_for_a_user_that_does_not_exist(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');

        $this->expectException(ModelNotFoundException::class);

        Livewire::actingAs($petugas)
            ->test(TransferPerizinan::class)
            ->dispatch('siap.prepare-transfer', nrp: '00000000', nama: 'Tidak Ada');
    }

    /**
     * @test
     */
    public function refuses_to_transfer_without_the_superadmin_role(): void
    {
        $petugas = $this->petugasWithPermissions([], '99999901');
        $target = $this->petugasWithRole('Role Lama Uji', '99999902');

        Livewire::actingAs($petugas)
            ->test(TransferPerizinan::class)
            ->set('nrp', '99999901')
            ->set('roles', ['Role Baru Uji'])
            ->set('checkedUsers', $this->checked('99999902'))
            ->call('save')
            ->assertDispatched('data-denied');

        $this->assertTrue($target->fresh()->hasRole('Role Lama Uji'));
    }

    /**
     * @test
     *
     * syncRoles() replaces the set: Role Lama Uji is not part of the
     * transferred roles, so it's removed from the target even though
     * nothing explicitly asked to remove it.
     */
    public function transfers_roles_and_permissions_to_the_checked_users(): void
    {
        $petugas = $this->petugasWithRole(config('permission.superadmin_name'), '99999901');
        $this->petugasWithRole('Role Lama Uji', '99999902');

        $roleBaru = Role::findOrCreate('Role Baru Uji', 'web');
        $permBaru = Permission::findOrCreate('uji.permission.baru', 'web');

        Livewire::actingAs($petugas)
            ->test(TransferPerizinan::class)
            ->set('nrp', '99999901')
            ->set('roles', [$roleBaru->id => $roleBaru->name])
            ->set('permissions', [$permBaru->id => $permBaru->name])
            ->set('checkedUsers', $this->checked('99999902'))
            ->call('save')
            ->assertDispatched('data-saved');

        $target = User::findByNRP('99999902');
        $this->assertTrue($target->hasRole('Role Baru Uji'));
        $this->assertFalse($target->hasRole('Role Lama Uji'));
        $this->assertTrue($target->hasPermissionTo('uji.permission.baru'));
    }
}
