<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\HakAkses;
use App\Models\Aplikasi\User;
use App\Support\Traits\Livewire\DeferredModal;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\LiveTable;
use Livewire\Component;

class SetHakAkses extends Component
{
    use Filterable, LiveTable, DeferredModal;

    public $nrp;

    public $nama;

    public $showChecked;

    public $checkedHakAkses;

    protected $listeners = [
        'khanza.show-sha' => 'showModal',
        'khanza.hide-sha' => 'hideModal',
        'khanza.prepare-user' => 'prepareUser',
        'khanza.simpan' => 'setHakAkses',
    ];

    protected function queryString()
    {
        return [
            'cari' => ['except' => '', 'as' => 'q'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getHakAksesKhanzaProperty()
    {
        return $this->isDeferred
            ? []
            : HakAkses::query()
            ->search($this->cari, ['nama_field', 'judul_menu'])
            ->when($this->showChecked, fn ($q) => $q->orWhereIn('nama_field', collect($this->checkedHakAkses)->filter()->keys()->all()))
            ->get();
    }

    public function render()
    {
        return view('livewire.user.khanza.set-hak-akses');
    }

    public function prepareUser(string $nrp = '', string $nama = '')
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function setHakAkses()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
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

    public function showModal()
    {
        $this->isDeferred = false;

        $user = User::rawFindByNRP($this->nrp);

        if (!$this->isDeferred) {
            $this->checkedHakAkses = collect($user->getAttributes())->except('id_user', 'password')
                ->filter(fn ($field) => $field === 'true')
                ->keys()
                ->mapWithKeys(fn ($field) => [$field => true])
                ->all();
        }

        $this->emit('$refresh');
    }

    public function hideModal()
    {
        $this->defaultValues();

        $this->emitUp('resetState');
    }

    public function defaultValues()
    {
        $this->undefer();

        $this->cari = '';
        $this->nrp = '';
        $this->nama = '';
        $this->showChecked = false;
        $this->checkedHakAkses = [];
    }
}
