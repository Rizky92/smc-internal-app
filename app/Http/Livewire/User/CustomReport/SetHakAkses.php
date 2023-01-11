<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $nrp;

    public $nama;

    public $customReportSearchRoleAndPermissions;

    public $customReportCheckedRoles;

    public $customReportCheckedPermissions;

    protected $listeners = [
        'customReportPrepareUser',
        'customReportSyncRolesAndPermissions',
        'resetModal',
    ];

    protected function queryString()
    {
        return [
            'customReportSearchRoleAndPermissions' => ['except' => '', 'as' => 'crsq'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.user.custom-report.set-hak-akses');
    }

    public function getAvailableRolesProperty()
    {
        return Role::query()
            ->with('permissions')
            ->when(! empty($this->customReportSearchRoleAndPermissions), function (Builder $query) {
                return $query->where('name', 'like', "%{$this->customReportSearchRoleAndPermissions}%");
            })
            ->orWhereIn('id', $this->customReportCheckedRoles)
            ->get();
    }

    public function getOtherAvailablePermissionsProperty()
    {
        return Permission::query()
            ->whereDoesntHave('roles')
            ->when(! empty($this->customReportSearchRoleAndPermissions), function (Builder $query) {
                return $query->where('name', 'like', "%{$this->customReportSearchRoleAndPermissions}%");
            })
            ->orWhereIn('id', $this->customReportCheckedPermissions)
            ->get();
    }

    public function customReportPrepareUser(string $nrp, string $nama)
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function customReportSyncRolesAndPermissions()
    {
        throw_if(!auth()->user()->hasRole(config('permission.superadmin_name')), AuthorizationException::class);

        $user = User::findByNRP($this->nrp);

        $user->syncRoles($this->customReportCheckedRoles);
        $user->syncPermissions($this->customReportCheckedPermissions);

        $this->emitTo('user.manajemen-user', 'flashSuccess', "Hak akses untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function resetModal()
    {
        $this->defaultValues();

        $this->dispatchBrowserEvent('hide.bs.modal');
    }

    private function defaultValues()
    {
        $this->nrp = '';
        $this->nama = '';
        $this->customReportSearchRoleAndPermissions = '';
        $this->customReportCheckedRoles = [];
        $this->customReportCheckedPermissions = [];
    }
}
