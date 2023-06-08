<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\HakAkses;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class SetHakAkses extends Component
{
    use Filterable, LiveTable, DeferredModal;

    /** @var ?string */
    public $nrp;

    /** @var ?string */
    public $nama;

    /** @var bool|false */
    public $showChecked;

    /** @var ?string[] */
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
     * @return \Illuminate\Database\Eloquent\Collection|array<empty, empty>
     */
    public function getHakAksesKhanzaProperty()
    {
        return $this->isDeferred
            ? []
            : HakAkses::query()
                ->search($this->cari, ['nama_field', 'judul_menu'])
                ->when($this->showChecked, fn (Builder $q): Builder => $q->orWhereIn('nama_field', collect($this->checkedHakAkses)->filter()->keys()->all()))
                ->get();
    }

    public function render(): View
    {
        return view('livewire.user.khanza.set-hak-akses');
    }

    public function prepareUser(string $nrp = '', string $nama = ''): void
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

        $this->forgetComputed();

        $hakAksesUser = $this->hakAksesKhanza
            ->mapWithKeys(fn ($hakAkses) => [$hakAkses->nama_field => $hakAkses->default_value])
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

        if (!$this->isDeferred) {
            $this->checkedHakAkses = collect($user->getAttributes())->except('id_user', 'password')
                ->filter(fn ($f, $k) => $f === 'true')
                ->keys()
                ->mapWithKeys(fn ($f, $k) => [$f => true])
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
