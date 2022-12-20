<?php

namespace App\Http\Livewire\User;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\FlashComponent;
use Livewire\Component;
use Livewire\WithPagination;

class SetHakAkses extends Component
{
    use WithPagination, FlashComponent;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'permission.updated' => 'updatePermissions',
    ];

    public function mount()
    {
        $this->perpage = 25;
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')->paginate($this->perpage);
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->pluck('name', 'id');
    }

    public function render()
    {
        return view('livewire.user.set-hak-akses')
            ->extends('layouts.admin', ['title' => 'Pengaturan Hak Akses'])
            ->section('content');
    }

    /**
     * @param  int $roleId
     * @param  array<int,int> $permissionIds
     */
    public function updatePermissions(int $roleId, array $permissionIds)
    {
        $role = Role::find($roleId);

        $role->syncPermissions($permissionIds);

        $this->flashSuccess('Hak akses berhasil diupdate');
    }
}
