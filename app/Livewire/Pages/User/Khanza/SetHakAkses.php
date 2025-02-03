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

    /** @var mixed */
    protected $listeners = [
        'khanza.show-sha'    => 'showModal',
        'khanza.hide-sha'    => 'hideModal',
        'khanza.prepare-set' => 'prepareUser',
        'khanza.set'         => 'save',
    ];

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
     * @return Collection|array<empty, empty>
     */
    public function getHakAksesKhanzaProperty()
    {
        return $this->isDeferred ? [] : HakAkses::query()
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

    public function prepareUser(string $nrp = '', string $nama = ''): void
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function save(): void
    {
        if (! user()->hasRole(config('permission.superadmin_name'))) {
            $this->dispatchBrowserEvent('data-denied');
            $this->emit('flash.error', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $hakAksesUser = $this->hakAksesKhanza
            ->mapWithKeys(fn (HakAkses $hakAkses): array => [$hakAkses->nama_field => $hakAkses->default_value])
            ->merge($this->checkedHakAkses)
            ->all();

        tracker_start('mysql_sik');

        User::rawFindByNRP($this->nrp)
            ->fill($hakAksesUser)
            ->save();

        tracker_end('mysql_sik');

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', "Hak akses SIMRS Khanza untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
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

        $this->emit('$refresh');
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
