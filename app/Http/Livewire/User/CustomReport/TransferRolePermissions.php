<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TransferRolePermissions extends Component
{
    public $deferLoading;

    public $nrp;

    public $nama;

    public $roles;

    public $permissions;

    public $cari;

    public $checkedUsers;

    protected $listeners = [
        'custom-report.show-trp' => 'showModal',
        'custom-report.hide-trp' => 'hideModal',
        'custom-report.prepare-transfer' => 'prepareTransfer',
        'custom-report.transfer' => 'transferRolePermissions',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.user.custom-report.transfer-role-permissions');
    }

    public function getAvailableUsersProperty()
    {
        return $this->deferLoading
            ? []
            : User::query()
            ->with('roles')
            ->where('petugas.nip', '!=', $this->nrp)
            ->where(function (Builder $query) {
                return $query
                    ->search($this->cari)
                    ->orWhereIn('petugas.nip', $this->checkedUsers);
            })
            ->get();
    }

    public function prepareTransfer(string $nrp = '', string $nama = '', array $roleIds = [], array $permissionIds = [])
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
        $this->roles = Role::whereIn('id', $roleIds)->pluck('name', 'id');
        $this->permissions = Permission::whereIn('id', $permissionIds)->pluck('name', 'id');
    }

    public function transferRolePermissions()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emitTo('user.manajemen-user', 'flashError', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $permittedUsers = User::whereIn('petugas.nip', $this->checkedUsers)
            ->get();

        tracker_start();

        foreach ($permittedUsers as $permittedUser) {
            $permittedUser->syncRoles($this->roles);
            $permittedUser->syncPermissions($this->permissions);
        }

        tracker_end();

        $this->emitTo('user.manajemen-user', 'flashSuccess', "Transfer hak akses Custom Report berhasil!");
    }

    public function showModal()
    {
        $this->deferLoading = false;
    }

    public function hideModal()
    {
        $this->defaultValues();
    }

    private function defaultValues()
    {
        $this->deferLoading = true;
        $this->nrp = '';
        $this->nama = '';
        $this->roles = [];
        $this->permissions = [];
        $this->cari = '';
        $this->checkedUsers = [];
    }
}
