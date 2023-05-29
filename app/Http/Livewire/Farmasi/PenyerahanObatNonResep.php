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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class PenyerahanObatNonResep extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataPenyerahanObatDriveThruProperty(): LengthAwarePaginator
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

    public function render(): View
    {
        return view('livewire.farmasi.penyerahan-obat-non-resep')
            ->layout(BaseLayout::class, ['title' => 'Persiapan Penyerahan Obat Pasien Rawat Jalan melalui Drive Thru']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
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
