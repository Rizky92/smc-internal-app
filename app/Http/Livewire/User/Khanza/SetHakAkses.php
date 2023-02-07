<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Models\Aplikasi\User;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $isDeffered;

    public $nrp;

    public $nama;

    public $selectedHakAkses;

    protected $listeners = [
        'khanza.show-sha' => 'showModal',
        'khanza.hide-sha' => 'hideModal',
        'khanza.prepare-user' => 'prepareUser',
        'khanza.simpan' => 'setHakAkses',
    ];

    public function mount()
    {
        $this->defaultValues();
    }

    public function getHakAksesKhanzaProperty()
    {
        return $this->isDeffered
            ? []
            : MappingAksesKhanza::pluck('judul_menu', 'nama_field');
    }

    public function render()
    {
        return view('livewire.user.khanza.set-hak-akses');
    }

    public function showModal()
    {
        $this->isDeffered = false;

        $user = User::rawFindByNRP($this->nrp);

        // if (! $this->isDeffered) {
        //     $this->selectedHakAkses = $this->hakAksesKhanza->keys()->filter(function ($field) use ($user) {
        //         return $user->getAttribute($field) === 'true';
        //     })->flatten()->toArray();
        // }
    }

    public function prepareUser(string $nrp = '', string $nama = '')
    {
        $this->nrp = $nrp;
        $this->nama = $nama;
    }

    public function setHakAkses()
    {
        if (!auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emitTo('user.manajemen-user', 'flashError', 'Anda tidak diizinkan untuk melakukan tindakan ini!');

            return;
        }

        $user = User::rawFindByNRP($this->nrp);

        tracker_start();

        foreach ($this->selectedHakAkses as $hakAkses) {
            $user->setAttribute($hakAkses, 'true');
        }

        $falsyHakAkses = $this->hakAksesKhanza->reject(function ($value, $key) {
            return in_array($key, $this->selectedHakAkses);
        });

        foreach ($falsyHakAkses as $field => $judul) {
            $user->setAttribute($field, 'false');
        }

        $user->save();

        tracker_end();

        $this->emitTo('user.manajemen-user', 'flashSuccess', "Hak akses SIMRS Khanza untuk user {$this->nrp} {$this->nama} berhasil diupdate!");
    }

    public function hideModal()
    {
        $this->defaultValues();
    }

    protected function defaultValues()
    {
        $this->isDeffered = true;
        $this->nrp = '';
        $this->nama = '';
        $this->selectedHakAkses = [];
    }
}
