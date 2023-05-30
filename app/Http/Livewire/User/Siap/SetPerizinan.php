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
    use DeferredModal;
    
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

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.user.siap.set-perizinan');
    }

    /**
     * @psalm-return \Illuminate\Database\Eloquent\Collection<\Illuminate\Database\Eloquent\Model>
     */
    public function getRolesProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return Role::with('permissions')->get();
    }

    public function getOtherPermissionsProperty()
    {
        return Permission::whereDoesntHave('roles')->get();
    }

    public function prepareUser(string $nrp = '', string $nama = '', array $roleIds = [], array $permissionIds = []): void
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
        $this->checkedRoles = $roleIds;
        $this->checkedPermissions = $permissionIds;
    }

    /**
     * @return void
     */
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

    public function defaultValues(): void
    {
        $this->undefer();

        $this->nrp = '';
        $this->nama = '';
        $this->checkedRoles = [];
        $this->checkedPermissions = [];
        $this->showChecked = false;
    }
}
