<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Antrian\AntriPoli;
use App\Models\Perawatan\Poliklinik;
use App\Models\Perawatan\RegistrasiPasien;
use Livewire\Component;

class AntreanPoli extends Component
{
    public Poliklinik $poli;

    public $kd_poli;

    public function mount($kd_poli)
    {
        $this->kd_poli = $kd_poli;
        $this->poli = Poliklinik::where('kd_poli', $kd_poli)->first();
    }

    public function getAntreanProperty()
    {
        return RegistrasiPasien::with(['dokterPoli', 'poliklinik', 'pasien'])
            ->where('kd_poli', $this->kd_poli)
            ->where('tgl_registrasi', now()->format('Y-m-d'))
            ->where('stts', 'Belum')
            ->orderBy('no_reg')
            ->get();
    }

    public function getNextAntreanProperty()
    {
        return AntriPoli::with(['dokter', 'poliklinik', 'registrasi'])->where('kd_poli', $this->kd_poli)->first();
    }

    public function call()
    {
        $nextAntrean = $this->getNextAntreanProperty();

        if ($nextAntrean && $nextAntrean->status == '1') {
            $this->dispatchBrowserEvent('play-voice', [
                'no_reg' => $nextAntrean->registrasi->no_reg,
                'nm_poli' => $nextAntrean->registrasi->poliklinik->nm_poli,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.pages.antrean.antrean-poli');
    }
}
