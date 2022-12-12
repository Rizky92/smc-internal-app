<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\Role;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $user;

    public $currentRoles;

    public $currentPermissions;

    public function getRolePermissionsProperty()
    {
        return Role::with('permissions')->get();
    }

    public function render()
    {
        return view('livewire.user.set-hak-akses');
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
