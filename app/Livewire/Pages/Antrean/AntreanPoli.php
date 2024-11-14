<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Perawatan\RegistrasiPasien;
use Livewire\Component;

class AntreanPoli extends Component
{
    public $kd_poli;

    public function mount($kd_poli)
    {
        $this->kd_poli = $kd_poli;
    }

    public function getAntreanProperty()
    {
        return RegistrasiPasien::with('dokterPoli')
            ->where('kd_poli', $this->kd_poli)
            ->where('tgl_registrasi', now()->format('Y-m-d'))
            ->where('stts', 'Belum')
            ->orderBy('no_reg')
            ->get();
    }

    public function render()
    {
        return view('livewire.pages.antrean.antrean-poli');
    }
}
