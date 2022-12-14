<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Livewire\Component;

class SetHakAkses extends Component
{
    /** @var \App\Models\Aplikasi\User $user */
    public $user;

    public $currentRoles;

    public $currentPermissions;

    protected $listeners = [
        'setUser',
    ];

    public function getRolePermissionsProperty()
    {
        return Role::with('permissions')->get();
    }

    public function render()
    {
        return view('livewire.user.set-hak-akses');
    }

    public function setUser($nrp)
    {
        $this->user = User::findByNRP($nrp)->load('roles.permissions');
    }

    public function syncRolePermissionsForUser(array $roles, array $permissions)
    {
        if (!empty($roles)) {
            $this->user->syncRoles($roles);
        }

        if (!empty($permissions)) {
            $this->user->syncPermissions($permissions);
        }
    }
}
