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

    public function prepare(int $id = -1)
    {
        $this->roleId = $id;

        if ($id !== -1) {
            $role = Role::findById($id);
    
            $this->roleName = $role->name;
            $this->checkedPermissions = $role->permissions->pluck('id', 'id')->all();
        }
    }

    public function create()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError();

            return;
        }

        tracker_start('mysql_smc');

        $role = Role::create([
            'name' => $this->roleName,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions(array_values($this->checkedPermissions));

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

        $role = Role::findById($this->roleId);

        tracker_start('mysql_smc');

        $role->name = $this->roleName;
        $role->save();

        $role->syncPermissions(array_values($this->checkedPermissions));

        tracker_end('mysql_smc');

        $this->emitUp('flash.success', "Hak akses {$this->roleName} berhasil diupdate!");
        $this->dispatchBrowserEvent('role-updated');
    }

    protected function defaultValues()
    {
        $this->roleId = -1;
        $this->roleName = '';
        $this->checkedPermissions = [];
    }
}
