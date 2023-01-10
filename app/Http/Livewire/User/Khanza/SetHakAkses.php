<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
use App\Models\Aplikasi\User;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $nrp;

    public $nama;

    public $khanzaCariHakAkses;

    public $khanzaCheckedHakAkses;

    protected $listeners = [
        'khanzaPrepareUser',
        'khanzaSyncHakAkses',
        'khanzaResetModal',
    ];

    protected function queryString()
    {
        return [
            'khanzaCariHakAkses' => ['except' => '', 'as' => 'hak_akses'],
        ];
    }

    public function mount()
    {
        $this->khanzaCariHakAkses = '';
        $this->khanzaCheckedHakAkses = [];
        $this->nrp = '';
        $this->nama = '';
    }

    public function getHakAksesKhanzaProperty()
    {
        return MappingAksesKhanza::query()
            ->where('nama_field', 'like', "%{$this->khanzaCariHakAkses}%")
            ->orWhere('judul_menu', 'like', "%{$this->khanzaCariHakAkses}%")
            ->orWhereIn('nama_field', $this->khanzaCheckedHakAkses)
            ->pluck('judul_menu', 'nama_field');
    }

    public function render()
    {
        return view('livewire.user.khanza.set-hak-akses');
    }

    public function khanzaPrepareUser(string $nrp = '', string $nama = '')
    {
        $this->nrp = $nrp;
        $this->nama = $nama;

        $user = User::rawFindByNRP($this->nrp);

        $this->khanzaCheckedHakAkses = [];

        foreach ($this->hakAksesKhanza as $field => $hakAkses) {
            if ($user->getAttribute($field) == 'true') {
                $this->khanzaCheckedHakAkses[] = $field;
            }
        }
    }

    public function khanzaSyncHakAkses(bool $hardTransfer = true)
    {
        if (! auth()->user()->hasRole(config('permission.superadmin_name'))) {
            $this->emit('flash', [
                'flash.type' => 'danger',
                'flash.message' => 'Anda tidak diizinkan untuk melakukan tindakan ini!',
            ]);

            return;
        }

        $user = User::rawFindByNRP($this->nrp);

        foreach ($this->khanzaCheckedHakAkses as $hakAkses) {
            $user->setAttribute($hakAkses, 'true');
        }

        if ($hardTransfer) {
            $khanzaCheckedHakAkses = $this->khanzaCheckedHakAkses;
            $falsyHakAkses = $this->hakAksesKhanza->reject(function ($value, $key) use ($khanzaCheckedHakAkses) {
                return in_array($key, $khanzaCheckedHakAkses);
            })->keys();

            foreach ($falsyHakAkses as $hakAkses) {
                $user->setAttribute($hakAkses, 'false');
            }
        }

        $user->save();

        $this->emit('flash', [
            'flash.type' => 'success',
            'flash.message' => "Hak akses SIMRS Khanza untuk user {$this->nrp} telah diupdate!",
        ]);

        $this->resetModal();
    }

    public function resetModal()
    {
        $this->khanzaCariHakAkses = '';
        $this->nrp = '';
        $this->nama = '';

        $this->dispatchBrowserEvent('hide.bs.modal');
    }
}
