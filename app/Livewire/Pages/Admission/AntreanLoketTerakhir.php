<?php

namespace App\Livewire\Pages\Admission;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class AntreanLoketTerakhir extends Component
{
    public function getAntreanTerakhirProperty(): Collection
    {
        return DB::connection('mysql_sik')->table('antriloketcetak_smc')
            ->select(DB::raw('LEFT(nomor, 1) as prefix'), DB::raw('MAX(nomor) as nomor'))
            ->whereNotNull('jam_panggil')
            ->whereDate('tanggal', now()->toDateString())
            ->groupBy(DB::raw('LEFT(nomor, 1)'))
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pages.admission.antrean-loket-terakhir');
    }
}
