<?php

namespace App\Http\Livewire\User\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\DeferredModal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SetPerizinan extends Component
{
    use DeferredModal;

    /** @var ?string */
    public $nrp;

    /** @var ?string */
    public $nama;

    /** @var ?array */
    public $checkedRoles;

    /** @var ?array */
    public $checkedPermissions;

    /** @var ?bool */
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

    public function render(): \Illuminate\View\View
    {
        return view('livewire.user.siap.set-perizinan');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Aplikasi\Role>
     */
    public function getRolesProperty(): Collection
    {
        return Role::with('permissions')->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<\App\Models\Aplikasi\Permission>
     */
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
        if (!Auth::user()->hasRole(config('permission.superadmin_name'))) {
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
