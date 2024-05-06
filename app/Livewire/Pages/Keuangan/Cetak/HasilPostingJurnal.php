<?php

namespace App\Livewire\Pages\Keuangan\Cetak;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\View\Components\CustomerLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class HasilPostingJurnal extends Component
{
    /** @var \Illuminate\Support\Collection */
    public $dataJurnal;

    /** 
     * @param  mixed  $dataJurnal
     */
    public function mount($dataJurnal): void
    {
        $this->dataJurnal = collect(json_decode(base64_decode($dataJurnal), true));
    }

    public function getRekeningProperty(): Collection
    {
        return Rekening::pluck('nm_rek', 'kd_rek');
    }

    public function getSIMRSSettingsProperty(): ?object
    {
        return DB::connection('mysql_sik')->table('setting')->first([
            'nama_instansi', 'alamat_instansi', 'kontak', 'email', 'logo'
        ]);
    }

    public function render(): View
    {
        $printJurnal = Jurnal::query()
            ->whereIn('no_jurnal', $this->dataJurnal->all())
            ->with('detail.rekening')
            ->get();

        return view('livewire.pages.keuangan.cetak.hasil-posting-jurnal', compact('printJurnal'))
            ->layout(CustomerLayout::class, ['title' => 'Cetak PDF Posting Jurnal']);
    }
}
