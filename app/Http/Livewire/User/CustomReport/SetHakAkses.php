<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use DB;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $nrp;

    public $nama;

    public $customReportCariPermissions;

    public $checkedRoles;

    public $checkedPermissions;

    protected $listeners = [
        'customReportPrepareUser',
        'customReportSyncRolesAndPermissions',
        'customReportResetModal',
    ];

    protected function queryString()
    {
        return [
            'customReportCariPermissions' => [
                'except' => '',
                'as' => 'cari_permission',
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

    public function customReportPrepareUser(string $nrp)
    {
        $this->nrp = $nrp;
    }

    public function customReportSyncRolesAndPermissions()
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

        $this->customReportResetModal();
    }

    public function customReportResetModal()
    {
        $this->defaultValues();

        $this->emitSelf('$refresh');
    }

    private function defaultValues()
    {
        $this->nrp = '';
        $this->nama = '';
        $this->customReportCariPermissions = '';
        $this->checkedRoles = [];
        $this->checkedPermissions = [];
    }
}
