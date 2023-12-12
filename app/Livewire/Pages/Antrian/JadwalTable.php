<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\Kepegawaian\Jadwal;

class JadwalTable extends Component
{
    public $jadwal;

    public function render()
    {
        $this->jadwal = Jadwal::all(); // Ganti dengan query sesuai kebutuhan
        return view('livewire.pages.antrian.jadwal-table');
    }
}
