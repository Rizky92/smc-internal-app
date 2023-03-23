<?php

namespace App\Http\Livewire\HakAkses\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;

class ModalPerizinan extends Component
{
    use Filterable, LiveTable, DeferredModal;

    public $roleId;

    public $roleName;

    public $checkedPermissions;

    protected $listeners = [
        'siap.prepare-create' => 'prepareCreate',
        'siap.prepare-update' => 'prepareUpdate',
        'siap.show' => 'showModal',
        'siap.hide' => 'hideModal',
        'siap.create-role' => 'createRole',
        'siap.update-role' => 'updateRole',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')
            ->pluck('name', 'id')
            ->groupBy(fn ($permission, $id) => Str::before($permission, '.'), $preserveKeys = true);
    }

    public function render()
    {
        return view('livewire.hak-akses.siap.modal-perizinan');
    }

    public function prepareCreate()
    {
        $this->defaultValues();
    }

    public function createRole()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emitUp('flash.error');

            return;
        }

        $validator = Validator::make([
            'roleName' => $this->roleName,
            'checkedPermissions' => ['array'],
            'checkedPermissions.*' => ['string'],
        ]);

        if ($validator->fails()) {
            $this->emitUp('flash.error');

            return;
        }

        tracker_start('mysql_smc');

        $role = Role::create([
            'name' => $this->roleName,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($this->checkedPermissions);

        tracker_end('mysql_smc');
    }
    
    public function prepareUpdate(int $roleId = -1, string $roleName = '', $permissionIds = [])
    {
        $this->roleId = $roleId;
        $this->roleName = $roleName;
        $this->checkedPermissions = collect($permissionIds)->mapWithKeys(fn ($v, $k) => [$v => $v])->toArray();
    }

    public function updateRole()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emitUp('flash.error');

            return;
        }

        $validator = Validator::make([
            'roleId' => $this->roleId,
            'roleName' => $this->roleName,
            'checkedPermissions' => $this->checkedPermissions,
        ], [
            'roleId' => ['required'],
            'roleName' => ['required', 'string', 'max:255'],
            'checkedPermissions' => ['array'],
            'checkedPermissions.*' => ['string', 'exists:App\Models\Aplikasi\Permission,id']
        ]);

        if ($validator->fails()) {
            $this->emit('flash.error');

            return;
        }

        tracker_start('mysql_smc');

        $role = Role::findById($this->roleId);
        $role->name = $this->roleName;

        $role->save();

        $role->syncPermissions($this->checkedPermissions);

        tracker_end('mysql_smc');
    }

    protected function defaultValues()
    {
        $this->roleId = -1;
        $this->roleName = '';
        $this->checkedPermissions = [];
    }
}
