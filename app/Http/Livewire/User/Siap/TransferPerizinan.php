<?php

namespace App\Http\Livewire\User\Siap;

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

    public $softTransfer;

    protected $listeners = [
        'siap.show-tp' => 'showModal',
        'siap.hide-tp' => 'hideModal',
        'siap.prepare-transfer' => 'prepareTransfer',
        'siap.transfer' => 'save',
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

    public function prepareTransfer(string $nrp = '', string $nama = '')
    {
        $this->nrp = $nrp;
        $this->nama = $nama;

        $user = User::query()
            ->with(['roles', 'permissions'])
            ->whereRaw('trim(pegawai.nik) = ?', $nrp)
            ->first();

        $this->roles = $user->roles->pluck('name', 'id')->all();
        $this->permissions = $user->permissions->pluck('name', 'id')->all();
    }

    public function save()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $selectedUsers = collect($this->checkedUsers)
            ->filter()
            ->map(fn ($_, $k) => strval($k))
            ->all();

        $selectedUsers = User::query()
            ->whereIn(DB::raw('trim(pegawai.nik)'), $selectedUsers)
            ->get();

        tracker_start('mysql_smc');

        foreach ($selectedUsers as $user) {
            $user->syncRoles($this->roles);
            $user->syncPermissions($this->permissions);
        }

        tracker_end('mysql_smc');

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', "Transfer perizinan SIAP berhasil!");
    }

    protected function defaultValues()
    {
        $this->undefer();

        $this->cari = '';
        $this->nrp = '';
        $this->nama = '';
        $this->showChecked = false;
        $this->softTransfer = false;
        $this->roles = [];
        $this->permissions = [];
        $this->checkedUsers = [];
    }
}
