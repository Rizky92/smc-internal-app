<?php

namespace App\Http\Livewire\HakAkses\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Support\Str;
use Livewire\Component;

class ModalUbahHakAkses extends Component
{
    use Filterable, LiveTable, DeferredModal;

    public $roleId;

    public $roleName;

    public $checkedPermissions;

    protected $listeners = [
        'siap.prepare' => 'prepare',
        'siap.show' => 'showModal',
        'siap.hide' => 'hideModal',
        'siap.save' => 'updateRolePermissions',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.hak-akses.siap.modal-ubah-hak-akses');
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')
            ->pluck('name', 'id')
            ->groupBy(fn ($permission, $id) => Str::before($permission, '.'), $preserveKeys = true);
    }

    public function prepare(int $roleId = -1, string $roleName = '', $permissionIds = [])
    {
        $this->roleId = $roleId;
        $this->roleName = $roleName;
        $this->checkedPermissions = $permissionIds;
    }

    public function updateRolePermissions()
    {
        dd($this->checkedPermissions);
        
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emitUp('flash.error', 'Anda tidak diizinkan untuk melakukan aksi ini!');

            return;
        }

        $role = Role::findById($this->roleId);

        tracker_start('mysql_smc');

        $role->syncPermissions($this->checkedPermissions);

        tracker_end('mysql_smc');

        $this->emitUp('flash.success', "Update perizinan untuk hak akses {$role->name} berhasil!");
    }

    protected function defaultValues()
    {
        $this->roleId = -1;
        $this->roleName = '';
        $this->checkedPermissions = [];
    }
}
