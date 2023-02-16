<?php

namespace App\Http\Livewire\User\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TransferPerizinan extends Component
{
    use Filterable, LiveTable;

    public $deferLoading;

    public $nrp;

    public $nama;

    public $roles;

    public $permissions;

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
        return view('livewire.user.siap.transfer-perizinan');
    }

    public function getAvailableUsersProperty()
    {
        return $this->deferLoading
            ? []
            : User::query()
            ->with('roles')
            ->where('pegawai.nik', '!=', $this->nrp)
            ->when(!empty($this->checkedUsers), fn (Builder $query) => $query->orWhereIn('pegawai.nik', $this->checkedUsers))
            ->search($this->cari)
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
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $permittedUsers = User::whereIn('pegawai.nik', $this->checkedUsers)->get();

        tracker_start('mysql_smc');

        foreach ($permittedUsers as $permittedUser) {
            $permittedUser->syncRoles($this->roles);
            $permittedUser->syncPermissions($this->permissions);
        }

        tracker_end('mysql_smc');

        $this->emit('flash.success', "Transfer hak akses Custom Report berhasil!");
    }

    public function showModal()
    {
        $this->deferLoading = false;
    }

    public function hideModal()
    {
        $this->defaultValues();
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];

        $this->deferLoading = true;
        $this->nrp = '';
        $this->nama = '';
        $this->roles = [];
        $this->permissions = [];
        $this->checkedUsers = [];
    }
}
