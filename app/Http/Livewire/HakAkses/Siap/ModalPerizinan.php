<?php

namespace App\Http\Livewire\HakAkses\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;

class ModalPerizinan extends Component
{
    use Filterable, LiveTable, DeferredModal, FlashComponent;

    public $roleId;

    public $roleName;

    public $checkedPermissions;

    protected $listeners = [
        'siap.prepare' => 'prepare',
        'siap.show' => 'showModal',
        'siap.hide' => 'hideModal',
        'siap.create' => 'create',
        'siap.update' => 'update',
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

    public function prepare(int $id = -1, string $name = '', array $permissionIds = [])
    {
        $this->roleId = $id;
        $this->roleName = $name;
        $this->checkedPermissions = $permissionIds;
    }

    public function create()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError();

            return;
        }

        $validator = Validator::make([
            'roleName' => $this->roleName,
            'checkedPermissions' => $this->checkedPermissions,
        ], [
            'roleName' => ['required', 'string', 'max:255'],
            'checkedPermissions' => ['required', 'array', 'min:0'],
            'checkedPermissions.*' => ['exists:App\Models\Aplikasi\Permission,id'],
        ]);

        if ($validator->fails()) {
            $this->flashError('Data tidak dapat divalidasi! Silahkan cek kembali data yang diinputkan!');

            return;
        }

        $input = $validator->validate();

        tracker_start('mysql_smc');

        $role = Role::create([
            'name' => $input['roleName'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($input['checkedPermissions']);

        tracker_end('mysql_smc');

        $this->emitUp('flash.success', 'Hak akses baru berhasil ditambahkan!');
        $this->dispatchBrowserEvent('role-created');
    }

    public function update()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError();

            return;
        }

        $validator = Validator::make([
            'roleId' => $this->roleId,
            'roleName' => $this->roleName,
            'checkedPermissions' => $this->checkedPermissions,
        ], [
            'roleId' => ['required', 'exists:App\Models\Aplikasi\Role,id'],
            'roleName' => ['required', 'string', 'max:255'],
            'checkedPermissions' => ['required', 'array', 'min:0'],
            'checkedPermissions.*' => ['exists:App\Models\Aplikasi\Permission,id'],
        ]);

        if ($validator->fails()) {
            $this->flashError('Data tidak dapat divalidasi! Silahkan cek kembali data yang diinputkan!');

            return;
        }

        $input = $validator->validate();

        $role = Role::findById($input['roleId']);

        tracker_start('mysql_smc');

        $role->name = $input['roleName'];
        $role->save();

        $role->syncPermissions($input['checkedPermissions']);

        tracker_end('mysql_smc');

        $this->emitUp('flash.success', "Hak akses {$input['roleName']} berhasil diupdate!");
        $this->dispatchBrowserEvent('role-updated');
    }

    protected function defaultValues()
    {
        $this->roleId = -1;
        $this->roleName = '';
        $this->checkedPermissions = [];
    }
}
