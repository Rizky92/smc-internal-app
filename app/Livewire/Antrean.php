<?php

namespace App\Livewire;

use App\Models\Perawatan\Poliklinik;
use Livewire\Component;

class Antrean extends Component
{
    public function getPoliProperty()
    {
        $today = strtoupper(carbon()->now()->translatedFormat('l'));

        return Poliklinik::with(['dokter' => function ($query) use ($today) {
            $query->where('jadwal.hari_kerja', $today);
        }])->whereHas('dokter', function ($query) use ($today) {
            $query->where('jadwal.hari_kerja', $today);
        })->get();
    }
    public function render()
    {
        return view('livewire.antrean');
    }
}
