<?php

namespace App\Livewire\Pages\User\Siap;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\LiveTable;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class TransferPerizinan extends Component
{
    use DeferredModal;
    use Filterable;
    use LiveTable;

    /** @var ?string */
    public $nrp;

    /** @var ?string */
    public $nama;

    /** @var ?string[] */
    public $roles;

    /** @var ?string[] */
    public $permissions;

    /** @var bool */
    public $showChecked;

    /** @var ?string[] */
    public $checkedUsers;

    /** @var bool */
    public $softTransfer;

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.user.siap.transfer-perizinan');
    }

    /**
     * @return Collection<User>|array<empty, empty>
     */
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
                ->whereRaw('trim(pegawai.nik) != ?', [$this->nrp])
                ->where(fn (Builder $q): Builder => $q
                    ->search($this->cari)
                    ->when($this->showChecked, fn (Builder $q): Builder => $q->orWhereIn(DB::raw('trim(pegawai.nik)'), $checkedUsers)))
                ->get();
    }

    #[On('siap.prepare-transfer')]
    public function prepareTransfer(string $nrp = '', string $nama = ''): void
    {
        $this->nrp = $nrp;
        $this->nama = $nama;

        $user = User::query()
            ->with(['roles', 'permissions'])
            ->whereRaw('trim(pegawai.nik) = ?', $nrp)
            ->first();

        if (! $user) {
            throw (new ModelNotFoundException)->setModel(User::class, [$nrp]);
        }

        $this->roles = $user->roles->pluck('name', 'id')->all();
        $this->permissions = $user->permissions->pluck('name', 'id')->all();
    }

    #[On('siap.transfer')]
    public function save(): void
    {
        if (! user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatch('data-denied');
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

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

        $this->dispatch('data-saved');
        $this->dispatch('flash.success', 'Transfer perizinan SIAP berhasil!');
    }

    protected function defaultValues(): void
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
