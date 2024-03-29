<?php

namespace App\Livewire\Pages\User\Siap;

use App\Livewire\Concerns\DeferredModal;
use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class SetPerizinan extends Component
{
    use DeferredModal;

    /** @var string */
    public $nrp;

    /** @var string */
    public $nama;

    /** @var array */
    public $checkedRoles;

    /** @var array */
    public $checkedPermissions;

    /** @var bool */
    public $showChecked;

    protected $listeners = [
        'siap.show-sp'     => 'showModal',
        'siap.hide-sp'     => 'hideModal',
        'siap.prepare-set' => 'prepareUser',
        'siap.set'         => 'save',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.user.siap.set-perizinan');
    }

    public function getRolesProperty(): Collection
    {
        return Role::with('permissions')->get();
    }

    public function getOtherPermissionsProperty(): Collection
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

    public function save(): void
    {
        if (! user()->hasRole(config('permission.superadmin_name'))) {
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
