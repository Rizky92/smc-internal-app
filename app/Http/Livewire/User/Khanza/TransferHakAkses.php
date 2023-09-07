<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\User;
use App\Support\Livewire\Concerns\DeferredModal;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\LiveTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class TransferHakAkses extends Component
{
    use Filterable, LiveTable, DeferredModal;

    /** @var ?string */
    public $nrp;

    /** @var ?string */
    public $nama;

    /** @var bool|false */
    public $showChecked;

    /** @var ?string[] */
    public $checkedUsers;

    /** @var bool|false */
    public $softTransfer;

    /** @var mixed */
    protected $listeners = [
        'khanza.show-tha'         => 'showModal',
        'khanza.hide-tha'         => 'hideModal',
        'khanza.prepare-transfer' => 'prepareTransfer',
        'khanza.transfer'         => 'save',
    ];

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|array<empty, empty>
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
            ->where(DB::raw('trim(pegawai.nik)'), '!=', $this->nrp)
            ->where(
                fn (Builder $q): Builder => $q
                    ->search($this->cari)
                    ->when($this->showChecked, fn (Builder $q): Builder => $q->orWhereIn(DB::raw('trim(pegawai.nik)'), $checkedUsers))
            )
            ->get();
    }

    public function render(): View
    {
        return view('livewire.user.khanza.transfer-hak-akses');
    }

    public function prepareTransfer(string $nrp = '', string $nama = ''): void
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function save(): void
    {
        if (!Auth::user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $currentUser = User::rawFindByNRP($this->nrp);

        $hakAkses = collect($currentUser->getAttributes())
            ->except(['id_user', 'password'])
            ->when($this->softTransfer, fn (Collection $c): Collection => $c->filter(fn ($v): bool => $v === 'true'))
            ->all();

        tracker_start('mysql_sik');

        User::query()
            ->whereIn(DB::raw('trim(pegawai.nik)'), collect($this->checkedUsers)->filter()->map(fn (bool $_, string $k): string => strval($k))->all())
            ->update($hakAkses);

        tracker_end('mysql_sik');

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', "Transfer hak akses SIMRS Khanza berhasil!");
    }

    protected function defaultValues(): void
    {
        $this->undefer();

        $this->cari = '';
        $this->nrp = '';
        $this->nama = '';
        $this->showChecked = false;
        $this->softTransfer = false;
        $this->checkedUsers = [];
    }
}
