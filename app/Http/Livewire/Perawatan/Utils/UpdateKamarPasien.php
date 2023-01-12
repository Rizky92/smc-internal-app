<?php

namespace App\Http\Livewire\Perawatan\Utils;

use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

class UpdateKamarPasien extends Component
{
    public $deferLoading;

    public $kamar;

    public $noRawat;

    public $tglMasuk;

    public $jamMasuk;

    public $hargaKamar;

    public function mount()
    {
        $this->defaultValues();
    }

    public function render()
    {
        return view('livewire.perawatan.utils.update-kamar-pasien');
    }

    public function openModal()
    {
        $this->deferLoading = false;
    }

    public function updateHargaKamarPasien()
    {
        throw_if(! auth()->user()->can('perawatan.daftar-pasien-ranap.update-biaya-ranap'), AuthorizationException::class, 'Anda tidak diizinkan untuk melakukan tindakan ini!');

        
    }

    public function closeModal()
    {
        $this->defaultValues();
    }

    private function defaultValues()
    {
        $this->deferLoading = true;
        $this->kamar = '';
        $this->noRawat = '';
        $this->tglMasuk = '';
        $this->jamMasuk = '';
        $this->hargaKamar = 0;
    }
}
