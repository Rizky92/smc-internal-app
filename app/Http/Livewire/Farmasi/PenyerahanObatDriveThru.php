<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PenyerahanObatDriveThru extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public $tglAwal;

    public $tglAkhir;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataPenyerahanObatDriveThruProperty()
    {
        return ResepObat::query()
            ->penyerahanMelaluiDriveThru($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'resep_obat.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'reg_periksa.umurdaftar',
                'reg_periksa.sttsumur',
                'resep_obat.status',
            ])
            ->sortWithColumns($this->sortColumns, [
                'waktu_peresepan' => DB::raw('timestamp(resep_obat.tgl_peresepan, resep_obat.jam_peresepan)'),
                'waktu_validasi' => DB::raw('timestamp(resep_obat.tgl_validasi, resep_obat.jam)'),
                'waktu_penyerahan' => DB::raw('timestamp(resep_obat.tgl_penyerahan, resep_obat.jam_penyerahan)'),
            ])
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.penyerahan-obat-drive-thru')
            ->layout(BaseLayout::class, ['title' => 'Persiapan Penyerahan Obat Pasien Rawat Jalan melalui Drive Thru']);
    }

    protected function defaultValues()
    {
        $this->tglAwal = now()->format('Y-m-d');
        $this->tglAkhir = now()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
