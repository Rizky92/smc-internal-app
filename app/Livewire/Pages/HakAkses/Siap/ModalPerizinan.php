<?php

namespace App\Livewire\Pages\HakAkses\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class ModalPerizinan extends Component
{
    use Filterable, LiveTable, DeferredModal, FlashComponent;

    /** @var int */
    public $roleId;

    /** @var string */
    public $roleName;

    /** @var array */
    public $checkedPermissions;

    /** @var mixed */
    protected $listeners = [
        'siap.prepare' => 'prepare',
        'siap.show' => 'showModal',
        'siap.hide' => 'hideModal',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function getPermissionsProperty(): Collection
    {
        return Permission::orderBy('name')
            ->pluck('name', 'id')
            ->groupBy(fn (string $permission, int $_) => Str::before($permission, '.'), $preserveKeys = true);
    }

    public function render(): View
    {
        return view('livewire.pages.hak-akses.siap.modal-perizinan');
    }

    public function prepare(int $id = -1): void
    {
        $this->roleId = $id;

        if ($id !== -1) {
            /** @var \App\Models\Aplikasi\Role */
            $role = Role::findById($id);

            $this->roleName = $role->name;
            $this->checkedPermissions = $role->permissions->pluck('id', 'id')->all();
        }
    }

    public function create(): void
    {
        if (!Auth::user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError();

            return;
        }

        tracker_start();

        $role = Role::create([
            'name'       => $this->roleName,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions(array_values($this->checkedPermissions));

        tracker_end();

        $this->emitUp('flash.success', 'Hak akses baru berhasil ditambahkan!');
        $this->dispatchBrowserEvent('role-created');
    }

    public function update(): void
    {
        if (!Auth::user()->hasRole(config('permission.superadmin_name'))) {
            $this->flashError();

            return;
        }

        /** @var \App\Models\Aplikasi\Role */
        $role = Role::findById($this->roleId);

        tracker_start();

        $role->name = $this->roleName;
        $role->save();

        $role->syncPermissions(array_values($this->checkedPermissions));

        tracker_end();

        $this->emitUp('flash.success', "Hak akses {$this->roleName} berhasil diupdate!");
        $this->dispatchBrowserEvent('role-updated');
    }

    protected function defaultValues(): void
    {
        $this->roleId = -1;
        $this->roleName = '';
        $this->checkedPermissions = [];
    }
}
