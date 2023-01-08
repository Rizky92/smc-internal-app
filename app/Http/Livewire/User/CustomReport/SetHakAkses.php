<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $nrp;

    public $cariHakAkses;

    public $checkedRoles;

    public $checkedPermissions;

    protected $listeners = [
        'prepareUser',
        'syncRolesAndPermissions',
        'resetModal',
    ];

    protected function queryString()
    {
        return [
            'cariHakAkses' => [
                'except' => '',
                'as' => 'cari_hak_akses',
            ],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getRolesProperty()
    {
        return Role::with('permissions')->get();
    }

    public function getOtherPermissionsProperty()
    {
        $availablePermissions = $this->roles->map(function (Role $role) {
            return $role->permissions->map(function (Permission $permission) {
                return $permission->id;
            });
        })->flatten()->toArray();

        return Permission::whereNotIn('id', $availablePermissions)->get();
    }

    public function render()
    {
        return view('livewire.user.custom-report.set-hak-akses');
    }

    public function prepareUser(string $nrp)
    {
        $this->nrp = $nrp;
    }

    public function syncRolesAndPermissions()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emit('flash', [
                'flash.type' => 'danger',
                'flash.message' => 'Anda tidak diizinkan untuk melakukan aksi ini.',
            ]);

            return;
        }

        $user = User::findByNRP($this->nrp);

        $user->syncRoles($this->checkedRoles);
        $user->syncPermissions($this->checkedPermissions);

        $this->emit('flash', [
            'flash.type' => 'success',
            'flash.message' => "Hak akses untuk user {$this->nrp} berhasil diubah!",
        ]);

        $this->resetModal();
    }

    public function resetModal()
    {
        $this->defaultValues();

        $this->emitSelf('$refresh');
    }

    private function defaultValues()
    {
        $this->checkedRoles = [];
        $this->checkedPermissions = [];
    }
}
