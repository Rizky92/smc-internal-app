<?php

namespace App\Http\Livewire\User\Khanza;

use App\Models\Aplikasi\MappingAksesKhanza;
use Livewire\Component;

class SetHakAkses extends Component
{
    public $nrp;

    public $nama;

    public $khanzaCariHakAkses;

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
        $this->nrp = '';
        $this->nama = '';
    }

    public function getHakAksesKhanzaProperty()
    {
        return MappingAksesKhanza::query()
            ->where('nama_kolom', 'like', "%{$this->khanzaCariHakAkses}%")
            ->orWhere('judul_menu', 'like', "%{$this->khanzaCariHakAkses}%")
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.user.khanza.set-hak-akses');
    }

    public function resetModal()
    {
        $this->khanzaCariHakAkses = '';
        $this->nrp = '';
        $this->nama = '';
    }
}
