<?php

namespace App\Livewire\Pages\Informasi;

use App\Models\Antrian\Jadwal;
use Illuminate\View\View;
use Livewire\Component;

class DashboardDokter extends Component
{
    public $collection;

    public function mount()
    {
        $this->collection = Jadwal::with(['dokter.cutiAktif', 'poliklinik'])->whereHas('dokter', function ($query) {
            $query->where('status', '1');
        })
        ->orderBy('hari_kerja')
        ->get()
        ->groupBy(fn ($jadwal) => $jadwal->poliklinik->nm_poli) // Menggunakan callback untuk menghindari error
        ->map(fn ($jadwals) => $jadwals->groupBy('dokter.kd_dokter')->toArray()) // Konversi menjadi array
        ->toArray();
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.dashboard-dokter');
    }
}
