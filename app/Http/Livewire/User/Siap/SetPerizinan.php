<?php

namespace App\Http\Livewire\User\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Livewire\Component;

class SetPerizinan extends Component
{
    use Filterable, LiveTable, DeferredModal;
    
    public $nrp;

    public $nama;

    public $checkedRoles;

    public $checkedPermissions;

    public $showChecked;

    protected $listeners = [
        'siap.show-sp' => 'showModal',
        'siap.hide-sp' => 'hideModal',
        'siap.prepare-set' => 'prepareUser',
        'siap.set' => 'save',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.user.siap.set-perizinan');
    }

    public function getRolesProperty()
    {
        return $this->isDeferred
            ? []
            : Role::query()
                ->with('permissions')
                ->search($this->cari)
                ->when($this->showChecked, fn ($q) => $q->orWhereIn('id', collect($this->checkedRoles)->filter()->keys()->all()))
                ->get();
    }

    public function getPermissionsProperty()
    {
        return $this->isDeferred
            ? []
            : Permission::query()
                ->search($this->cari)
                ->when($this->showChecked, fn ($q) => $q->orWhereIn('id', collect($this->checkedPermissions)->filter()->keys()->all()))
                ->pluck('name', 'id');
    }

    public function prepareUser(string $nrp = '', string $nama = '', array $roleIds = [], array $permissionIds = [])
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
        $this->checkedRoles = $roleIds;
        $this->checkedPermissions = $permissionIds;
    }

    public function save()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $user = User::findByNRP($this->nrp);

        tracker_start('mysql_smc');

        $user->syncRoles($this->checkedRoles);
        $user->syncPermissions($this->checkedPermissions);

        tracker_end('mysql_smc');

        $this->emit('flash.success', "Perizinan SIAP untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function defaultValues()
    {
        $this->undefer();

        $this->nrp = '';
        $this->nama = '';
        $this->checkedRoles = [];
        $this->checkedPermissions = [];
        $this->showChecked = false;
    }
}
