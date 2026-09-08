<?php

namespace App\Livewire\Pages\User\Khanza;

use App\Livewire\Concerns\DeferredModal;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\LiveTable;
use App\Models\Aplikasi\HakAkses;
use App\Models\Aplikasi\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class SetHakAkses extends Component
{
    use DeferredModal;
    use Filterable;
    use LiveTable;

    /** @var string */
    public $nrp;

    /** @var string */
    public $nama;

    /** @var bool */
    public $showChecked;

    /** @var string[] */
    public $checkedHakAkses;

    protected function queryString(): array
    {
        return [
            'cari' => ['except' => '', 'as' => 'q'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * Always a Collection, empty while deferred.
     *
     * This used to hand back a plain array before the modal opened, which save()
     * then called ->mapWithKeys() on. khanza.set is a global event and can arrive
     * first, so that was a reachable fatal.
     */
    public function getHakAksesKhanzaProperty(): Collection
    {
        return $this->isDeferred ? new Collection : HakAkses::query()
            ->search($this->cari, ['nama_field', 'judul_menu'])
            ->when($this->showChecked, fn (Builder $q): Builder => $q
                ->orWhereIn('nama_field', collect($this->checkedHakAkses)->filter()->keys()->all())
            )
            ->sortWithColumns($this->sortColumns)
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.user.khanza.set-hak-akses');
    }

    #[On('khanza.prepare-set')]
    public function prepareUser(string $nrp = '', string $nama = ''): void
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    #[On('khanza.set')]
    public function save(): void
    {
        if (! user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatch('data-denied');
            $this->dispatch('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        // toBase() because mapWithKeys() on an *empty* Eloquent collection stays
        // an Eloquent collection — the downgrade only happens once it holds
        // something that is not a model — and merging booleans into one makes it
        // call getKey() on a bool.
        $hakAksesUser = $this->hakAksesKhanza
            ->mapWithKeys(fn (HakAkses $hakAkses): array => [$hakAkses->nama_field => $hakAkses->default_value])
            ->toBase()
            ->merge($this->checkedHakAkses)
            ->all();

        tracker_start('mysql_sik');

        User::rawFindByNRP($this->nrp)
            ->fill($hakAksesUser)
            ->save();

        tracker_end('mysql_sik');

        $this->dispatch('data-saved');
        $this->dispatch('flash.success', "Hak akses SIMRS Khanza untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function showModal(): void
    {
        $this->isDeferred = false;

        $user = User::rawFindByNRP($this->nrp);

        if (! $this->isDeferred) {
            $this->checkedHakAkses = collect($user->getAttributes())->except(['id_user', 'password'])
                ->filter(fn (?string $v, $_): bool => $v === 'true')
                ->keys()
                ->mapWithKeys(fn (string $v, $_): array => [$v => true])
                ->all();
        }

        $this->dispatch('$refresh');
    }

    public function defaultValues(): void
    {
        $this->undefer();

        $this->cari = '';
        $this->nrp = '';
        $this->nama = '';
        $this->showChecked = false;
        $this->checkedHakAkses = [];
    }
}
