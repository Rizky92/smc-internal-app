<?php

namespace App\Http\Livewire\User\CustomReport;

use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class TransferRolePermissions extends Component
{
    public $deferLoading = true;

    public $nrp;

    public $nama;

    public $roles;

    public $cari;

    public $checkedUsers;

    protected $listeners = [
        'custom-report.prepare' => 'prepareUser',
        'custom-report.transfer-permissions' => 'transferRolePermissions',
        'custom-report.reset-modal' => 'resetModal',
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
        return User::query()
            ->where('petugas.nip', '!=', $this->nrp)
            ->where(function (Builder $query) {
                return $query
                    ->search($this->cari)
                    ->orWhereIn('petugas.nip', $this->checkedUsers);
            })
            ->get();
    }

    public function prepareUser(string $nrp = '', string $nama = '')
    {
        $this->nrp = $nrp;
        $this->nama = $nama;

        $this->roles = User::findByNRP($nrp)->roles->map(function ($value) {
            return $value->name;
        });
    }

    public function transferRolePermissions()
    {

    }

    public function resetModal()
    {
        $this->defaultValues();
    }

    private function defaultValues()
    {
        $this->nrp = '';
        $this->nama = '';
        $this->cari = '';
        $this->checkedUsers = [];
        $this->roles = [];
    }
}