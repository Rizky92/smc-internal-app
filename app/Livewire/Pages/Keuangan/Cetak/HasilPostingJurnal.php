<?php

namespace App\Livewire\Pages\Keuangan\Cetak;

use App\Models\Keuangan\Jurnal\Jurnal;
use App\Models\Keuangan\Rekening;
use App\View\Components\CustomerLayout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class HasilPostingJurnal extends Component
{
    /** @var Collection */
    public $dataJurnal;

    protected function queryString(): array
    {
        return [
            'dataJurnal' => ['as' => 'data_jurnal'],
        ];
    }

    public function getRekeningProperty(): Collection
    {
        return Rekening::pluck('nm_rek', 'kd_rek');
    }

    public function getSIMRSSettingsProperty(): ?object
    {
        return DB::connection('mysql_sik')->table('setting')->first([
            'nama_instansi', 'alamat_instansi', 'kontak', 'email', 'logo',
        ]);
    }

    public function render(): View
    {
        $dataJurnalDicetak = collect(json_decode(base64_decode($this->dataJurnal), true))->all();

        $printJurnal = Jurnal::query()
            ->whereIn('no_jurnal', $dataJurnalDicetak)
            ->with('detail.rekening')
            ->get();

        return view('livewire.pages.keuangan.cetak.hasil-posting-jurnal', compact('printJurnal'))
            ->layout(CustomerLayout::class, ['title' => 'Cetak PDF Posting Jurnal']);
    }
}
