<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TransferHakAkses extends Component
{
    public $customReportCariUser;

    public $nrp;

    public $nama;

    public $roles;

    public $permissions;

    public $checkedUsers;

    protected function queryString()
    {
        return [
            'customReportCariUser' => [
                'except' => '',
                'as' => 'cari_user',
            ],
        ];
    }

    protected $listeners = [
        'prepareTransferCustomReport',
        'customReportTransferPermissions',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function getAvailableUsersProperty()
    {
        return User::query()
            ->where('petugas.nip', '!=', $this->nrp)
            ->search($this->customReportCariUser)
            ->when(!empty($this->checkedUsers), function (Builder $query) {
                return $query->orWhereIn('petugas.nip', $this->checkedUsers);
            })
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.user.custom-report.transfer-hak-akses');
    }

    private function defaultValues()
    {
        $this->customReportCariUser = '';
        $this->nrp = '';
        $this->nama = '';
        $this->roles = [];
        $this->permissions = [];
        $this->checkedUsers = [];
    }

    public function prepareTransfer(
        string $nrp,
        string $nama,
        array $roles = [],
        array $permissions = []
    ) {
        $this->nrp = $nrp;
        $this->nama = $nama;
        $this->roles = Role::whereIn('id', $roles)->pluck('name', 'id')->toArray();
        $this->permissions = Permission::whereIn('id', $permissions)->pluck('name', 'id')->toArray();
    }

    public function transferPermissions()
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emit('flash', [
                'flash.type' => 'danger',
                'flash.message' => 'Anda tidak diizinkan untuk melakukan aksi ini.',
            ]);

            $this->defaultValues();

            return;
        }

        $permittedUsers = User::whereIn('petugas.nip', $this->checkedUsers)->get();

        foreach ($permittedUsers as $user) {
            $user->assignRole($this->roles);
            $user->givePermissionTo($this->permissions);
        }

        $this->emit('flash', [
            'flash.type' => 'success',
            'flash.message' => "Transfer hak akses berhasil!",
        ]);

        $this->resetModal();
    }

    public function resetModal()
    {
        $this->defaultValues();
    }
}
