<?php

namespace App\Livewire\Pages\Informasi;

use App\Models\Kepegawaian\Dokter;
use Illuminate\View\View;
use Livewire\Component;

class DashboardDokter extends Component
{
    public $collection;

    public function mount()
    {
        $this->collection = Dokter::whereHas('jadwal')->with('jadwal')->where('status', '1')->get();
    }

    public function render(): View
    {
        return view('livewire.pages.informasi.dashboard-dokter');
    }
}
