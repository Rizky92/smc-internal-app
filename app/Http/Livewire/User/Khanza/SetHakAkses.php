<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Models\Aplikasi\User;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $deferLoading;

    public $nrp;

    public $nama;

    public $cari;

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
        return $this->deferLoading
            ? []
            : MappingAksesKhanza::query()
            ->where('nama_field', 'like', "%{$this->cari}%")
            ->orWhere('judul_menu', 'like', "%{$this->cari}%")
            ->orWhereIn('nama_field', $this->checkedHakAkses)
            ->pluck('judul_menu', 'nama_field');
    }

    public function render()
    {
        return view('livewire.user.khanza.set-hak-akses');
    }

    public function showModal()
    {
        $this->deferLoading = false;

        $user = User::rawFindByNRP($this->nrp);

        if (! $this->deferLoading) {
            $this->checkedHakAkses = $this->hakAksesKhanza
                ->keys()
                ->filter(fn ($field) => $user->gettAttribute($field) === 'true')
                ->flatten()
                ->toArray();
        }
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

        tracker_start();

        foreach ($this->checkedHakAkses as $hakAkses) {
            $user->setAttribute($hakAkses, 'true');
        }

        $falsyHakAkses = $this->hakAksesKhanza->reject(function ($value, $key) {
            return in_array($key, $this->checkedHakAkses);
        });

        foreach ($falsyHakAkses as $field => $judul) {
            $user->setAttribute($field, 'false');
        }

        $user->save();

        tracker_end();

        $this->dispatchBrowserEvent('data-saved');
        $this->emit('flash.success', "Hak akses SIMRS Khanza untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function hideModal()
    {
        $this->defaultValues();
    }

    public function defaultValues()
    {
        $this->deferLoading = true;
        $this->nrp = '';
        $this->nama = '';
        $this->cari = '';
        $this->checkedHakAkses = [];
    }
}
