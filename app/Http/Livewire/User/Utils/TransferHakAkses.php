<?php

namespace App\Http\Livewire\User\Utils;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TransferHakAkses extends Component
{
    public $cariUser;

    public $nrp;

    public $nama;

    public $roles;

    public $permissions;

    public $checkedUsers;

    protected function queryString()
    {
        return [
            'cariUser' => [
                'except' => '',
                'as' => 'cari_user',
            ],
        ];
    }

    protected $listeners = [
        'prepareTransfer',
        'transferPermissions',
    ];

    public function mount()
    {
        $this->cariUser = '';
        $this->nrp = '';
        $this->nama = '';
        $this->roles = [];
        $this->permissions = [];
        $this->checkedUsers = [];
    }

    public function getAvailableUsersProperty()
    {
        return User::query()
            ->where('petugas.nip', '!=', $this->nrp)
            ->search($this->cariUser)
            ->when(!empty($this->checkedUsers), function (Builder $query) {
                return $query->orWhereIn('petugas.nip', $this->checkedUsers);
            })
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.user.utils.transfer-hak-akses');
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
        $this->cariUser = '';
        $this->nrp = '';
        $this->nama = '';
        $this->roles = [];
        $this->permissions = [];
        $this->checkedUsers = [];
    }
}
