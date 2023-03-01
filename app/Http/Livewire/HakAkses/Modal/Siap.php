<?php

namespace App\Http\Livewire\HakAkses\Modal;

use App\Models\Aplikasi\Permission;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Support\Str;
use Livewire\Component;

class Siap extends Component
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
        return view('livewire.hak-akses.modal.siap');
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

    protected function defaultValues()
    {
        $this->roleId = -1;
        $this->roleName = '';
        $this->checkedPermissions = [];
    }
}