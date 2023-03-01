<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
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

    public $cari;

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
            : MappingAksesKhanza::query()
                ->search($this->cari, ['nama_field', 'judul_menu'])
                ->when($this->showChecked, fn ($q) => $q->orWhereIn('nama_field', $this->checkedHakAkses))
                ->pluck('judul_menu', 'nama_field');
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

        $user = User::rawFindByNRP($this->nrp);

        tracker_start('mysql_sik');

        foreach ($this->checkedHakAkses as $hakAkses) {
            $user->setAttribute($hakAkses, 'true');
        }

        $falsyHakAkses = $this->hakAksesKhanza->reject(fn ($_, $key) => in_array($key, $this->checkedHakAkses));

        foreach ($falsyHakAkses as $field => $judul) {
            $user->setAttribute($field, 'false');
        }

        $user->save();

        tracker_end('mysql_sik');

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', "Hak akses SIMRS Khanza untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function showModal()
    {
        $this->loadProperties();

        $user = User::rawFindByNRP($this->nrp);

        if (! $this->isDeferred) {
            $this->checkedHakAkses = $this->hakAksesKhanza
                ->keys()
                ->filter(fn ($field) => $user->getAttribute($field) === 'true')
                ->flatten()
                ->toArray();
        }
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
