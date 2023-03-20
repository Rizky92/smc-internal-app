<?php

namespace App\Http\Livewire\User\Siap;

use App\Models\Aplikasi\Permission;
use App\Models\Aplikasi\Role;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransferPerizinan extends Component
{
    use Filterable, LiveTable, DeferredModal;

    public $nrp;

    public $nama;

    public $roles;

    public $permissions;

    public $showChecked;

    public $checkedUsers;

    protected $listeners = [
        'siap.show-tp' => 'showModal',
        'siap.hide-tp' => 'hideModal',
        'siap.prepare-transfer' => 'prepareTransfer',
        'siap.transfer' => 'transferRolePermissions',
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
        $checkedUsers = collect($this->checkedUsers)
            ->filter()
            ->keys()
            ->all();

        return $this->isDeferred
            ? []
            : User::query()
                ->with('roles')
                ->where(DB::raw('trim(pegawai.nik)'), '!=', $this->nrp)
                ->where(fn ($q) => $q
                    ->search($this->cari)
                    ->when($this->showChecked, fn (Builder $query) => $query->orWhereIn(DB::raw('trim(pegawai.nik)'), $checkedUsers)))
                ->get();
    }

    public function prepareTransfer($data)
    {
        $this->nrp = $data['nrp'];
        $this->nama = $data['nama'];
        $this->roles = $data['roleIds'];
        $this->permissions = $data['permissionIds'];
    }

    public function transferRolePermissions()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $permittedUsers = User::whereIn(DB::raw('trim(pegawai.nik)'), $this->checkedUsers)->get();

        tracker_start('mysql_smc');

        foreach ($permittedUsers as $permittedUser) {
            $permittedUser->syncRoles($this->roles);
            $permittedUser->syncPermissions($this->permissions);
        }

        tracker_end('mysql_smc');

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', "Transfer perizinan SIAP berhasil!");
    }

    public function showModal()
    {
        $this->loadProperties();
    }

    public function hideModal()
    {
        $this->defaultValues();

        $this->emitUp('resetState');
    }

    protected function defaultValues()
    {
        $this->undefer();

        $this->cari = '';
        $this->nrp = '';
        $this->nama = '';
        $this->showChecked = false;
        $this->roles = [];
        $this->permissions = [];
        $this->checkedUsers = [];
    }
}
