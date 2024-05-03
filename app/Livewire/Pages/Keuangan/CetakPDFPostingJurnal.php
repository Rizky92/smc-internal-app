<?php

namespace App\Livewire\Pages\Keuangan;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\View\Components\CustomerLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Livewire\Component;

class CetakPDFPostingJurnal extends Component
{
    public $savedData;

    public function mount()
    {
        $this->savedData = session()->pull('savedData');
    }

    public function getRekeningProperty(): Collection
    {
        return Rekening::pluck('nm_rek', 'kd_rek');
    }

    public function getSIMRSSettingsProperty(): object
    {
        return DB::connection('mysql_sik')->table('setting')->first([
            'nama_instansi', 'alamat_instansi', 'kontak', 'email', 'logo'
        ]);
    }

    public function render()
    {
        $dataJurnal = Jurnal::query()
            ->whereIn('no_jurnal', collect($this->savedData)->pluck('no_jurnal')->all())
            ->with('detail.rekening')
            ->get();

        return view('livewire.pages.keuangan.cetak-p-d-f-posting-jurnal', compact('dataJurnal'))
            ->layout(CustomerLayout::class, ['title' => 'Cetak PDF Posting Jurnal']);
    }
}
