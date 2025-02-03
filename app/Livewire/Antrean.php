<?php

namespace App\Livewire;

use App\Models\Perawatan\Poliklinik;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\View;
use Livewire\Component;

class Antrean extends Component
{
    /**
     * @psalm-return Collection<Model>
     */
    public function getPoliProperty(): Collection
    {
        $today = strtoupper(carbon()->now()->translatedFormat('l'));

        return Poliklinik::with(['dokter' => function ($query) use ($today) {
            $query->where('jadwal.hari_kerja', $today);
        }])->whereHas('dokter', function ($query) use ($today) {
            $query->where('jadwal.hari_kerja', $today);
        })->get();
    }

    public function render(): View
    {
        return view('livewire.antrean');
    }
}
