<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class SetRolePermissions extends Component
{
    public $deferLoading;

    public $nrp;

    public $nama;

    public $cari;

    public $checkedRoles;

    public $checkedPermissions;

    protected $listeners = [
        'custom-report.open-modal' => 'openModal',
        'custom-report.prepare' => 'prepareUser',
        'custom-report.set-permissions' => 'setRolePermissions',
        'custom-report.close-modal' => 'closeModal',
    ];

    protected function queryString()
    {
        return [
            'cari' => ['except' => '', 'as' => 'q'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.user.custom-report.set-role-permissions');
    }

    public function getAvailableRolesProperty()
    {
        return $this->deferLoading
            ? []
            : Role::with('permissions')
            ->when(!empty($this->cari), function (Builder $query) {
                return $query->where('name', 'like', "%{$this->cari}%")
                    ->orWhereIn('id', $this->checkedRoles);
            })
            ->get();
    }

    public function getOtherPermissionsProperty()
    {
        return $this->deferLoading
            ? []
            : Permission::whereDoesntHave('roles')
            ->when(!empty($this->cari), function (Builder $query) {
                return $query->where('name', 'like', "%{$this->cari}%")
                    ->orWhereIn('id', $this->checkedPermissions);
            })
            ->get();
    }

    public function prepareUser(string $nrp, string $nama, array $roleIds, array $permissionIds)
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
        $this->checkedRoles = $roleIds;
        $this->checkedPermissions = $permissionIds;
    }

    public function openModal()
    {
        $this->deferLoading = false;
    }

    public function setRolePermissions()
    {
        throw_if(!auth()->user()->hasRole(config('permission.superadmin_name')), AuthorizationException::class);

        $user = User::findByNRP($this->nrp);

        $user->syncRoles($this->checkedRoles);
        $user->syncPermissions($this->checkedPermissions);

        $this->emitTo('user.manajemen-user', 'flashSuccess', "Hak akses untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function closeModal()
    {
        $this->defaultValues();
    }

    private function defaultValues()
    {
        $this->deferLoading = true;
        $this->nrp = '';
        $this->nama = '';
        $this->cari = '';
        $this->checkedRoles = [];
        $this->checkedPermissions = [];
    }
}
