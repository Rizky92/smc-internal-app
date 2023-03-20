<?php

namespace App\Http\Livewire\User\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\DeferredModal;
use Livewire\Component;

class SetPerizinan extends Component
{
    use DeferredModal;
    
    public $nrp;

    public $nama;

    public $checkedRoles;

    public $checkedPermissions;

    protected $listeners = [
        'siap.show-sp' => 'showModal',
        'siap.hide-sp' => 'hideModal',
        'siap.prepare-set' => 'prepareUser',
        'siap.save' => 'setRolePermissions',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.user.siap.set-perizinan');
    }

    public function getAvailableRolesProperty()
    {
        return Role::with('permissions')->get();
    }

    public function getOtherPermissionsProperty()
    {
        return Permission::whereDoesntHave('roles')->get();
    }

    public function prepareUser($data)
    {
        $this->nrp = $data['nrp'];
        $this->nama = $data['nama'];
        $this->checkedRoles = $data['roleIds'];
        $this->checkedPermissions = $data['permissionIds'];
    }

    public function setRolePermissions()
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

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', "Perizinan SIAP untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }
    
    public function hideModal()
    {
        $this->defaultValues();

        $this->emitUp('resetState');
    }

    public function defaultValues()
    {
        $this->undefer();

        $this->nrp = '';
        $this->nama = '';
        $this->checkedRoles = [];
        $this->checkedPermissions = [];
    }
}
