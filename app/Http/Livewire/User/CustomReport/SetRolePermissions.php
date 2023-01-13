<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

class SetRolePermissions extends Component
{
    public $nrp;

    public $nama;

    public $checkedRoles;

    public $checkedPermissions;

    protected $listeners = [
        'custom-report.prepare-user' => 'prepareUser',
        'custom-report.set-permissions' => 'setRolePermissions',
        'custom-report.close-modal' => 'defaultValues',
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

    public function prepareUser(string $nrp, string $nama, array $roleIds, array $permissionIds)
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
        $this->checkedRoles = $roleIds;
        $this->checkedPermissions = $permissionIds;
    }

    public function setRolePermissions()
    {
        throw_if(!auth()->user()->hasRole(config('permission.superadmin_name')), AuthorizationException::class);

        $user = User::findByNRP($this->nrp);

        $user->syncRoles($this->checkedRoles);
        $user->syncPermissions($this->checkedPermissions);

        $this->emitTo('user.manajemen-user', 'flashSuccess', "Hak akses untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function defaultValues()
    {
        $this->nrp = '';
        $this->nama = '';
        $this->checkedRoles = [];
        $this->checkedPermissions = [];
    }
}
