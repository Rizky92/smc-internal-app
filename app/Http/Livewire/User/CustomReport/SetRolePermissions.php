<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Livewire\Component;

class SetRolePermissions extends Component
{
    public $deferLoading;

    public $nrp;

    public $nama;

    public $checkedRoles;

    public $checkedPermissions;

    protected $listeners = [
        'custom-report.show-srp' => 'showModal',
        'custom-report.hide-srp' => 'hideModal',
        'custom-report.prepare-user' => 'prepareUser',
        'custom-report.save' => 'setRolePermissions',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.user.custom-report.set-role-permissions');
    }

    public function getAvailableRolesProperty()
    {
        return Role::with('permissions')->get();
    }

    public function getOtherPermissionsProperty()
    {
        return Permission::whereDoesntHave('roles')->get();
    }

    public function prepareUser(string $nrp = '', string $nama = '', array $roleIds = [], array $permissionIds = [])
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
        $this->checkedRoles = $roleIds;
        $this->checkedPermissions = $permissionIds;
    }

    public function setRolePermissions()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emitUp('flashError', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $user = User::findByNRP($this->nrp);

        tracker_start('mysql_smc');

        $user->syncRoles($this->checkedRoles);
        $user->syncPermissions($this->checkedPermissions);

        tracker_end('mysql_smc');

        $this->dispatchBrowserEvent('saved');
        $this->emitUp('flashSuccess', "Hak akses untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function showModal()
    {
        $this->deferLoading = false;
    }

    public function hideModal()
    {
        $this->defaultValues();
    }

    public function defaultValues()
    {
        $this->deferLoading = true;
        $this->nrp = '';
        $this->nama = '';
        $this->checkedRoles = [];
        $this->checkedPermissions = [];
    }
}
