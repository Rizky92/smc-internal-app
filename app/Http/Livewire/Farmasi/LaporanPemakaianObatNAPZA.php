<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Obat;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPemakaianObatNAPZA extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var ?string */
    public $tglAwal;

    /** @var ?string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return array<empty, empty>|\Illuminate\Contracts\Pagination\Paginator
     */
    public function getDataPemakaianObatNarkotikaProperty()
    {
        return $this->isDeferred
            ? []
            : Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'narkotika')
                ->search($this->cari, [
                    'databarang.kode_brng',
                    'databarang.nama_brng',
                    'databarang.kode_golongan',
                    'golongan_barang.nama',
                    'kodesatuan.kode_sat',
                    'kodesatuan.satuan',
                ])
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage, ['*'], 'page_narkotika');
    }

    /**
     * @return array<empty, empty>|\Illuminate\Contracts\Pagination\Paginator
     */
    public function getDataPemakaianObatPsikotropikaProperty()
    {
        return $this->isDeferred
            ? []
            : Obat::query()
                ->pemakaianObatNAPZA($this->tglAwal, $this->tglAkhir, 'psikotropika')
                ->search($this->cari, [
                    'databarang.kode_brng',
                    'databarang.nama_brng',
                    'databarang.kode_golongan',
                    'golongan_barang.nama',
                    'kodesatuan.kode_sat',
                    'kodesatuan.satuan',
                ])
                ->sortWithColumns($this->sortColumns)
                ->paginate($this->perpage, ['*'], 'page_psikotropika');
    }

    public function render(): View
    {
        return view('livewire.farmasi.laporan-pemakaian-obat-napza')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pemakaian Obat NAPZA']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
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
