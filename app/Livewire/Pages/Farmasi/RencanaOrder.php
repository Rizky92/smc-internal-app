<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\Obat;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class RencanaOrder extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var int 12|14 */
    public $periode;

    protected function queryString(): array
    {
        return [
            'periode' => ['as' => 'hari'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getStokDaruratObatProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->daruratStok($this->periode)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.rencana-order')
            ->layout(BaseLayout::class, ['title' => 'Rencana Order Farmasi']);
    }

    protected function defaultValues(): void
    {
        $this->periode = 14;
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => Obat::query()
                ->daruratStok($this->periode)
                ->cursor()
                ->map(fn (Obat $model): array => [
                    'nama_brng'                                 => $model->nama_brng,
                    'satuan_kecil'                              => $model->satuan_kecil,
                    'kategori'                                  => $model->kategori,
                    'stok_minimal'                              => $model->stokminimal,
                    'stok_sekarang_ifi'                         => $model->stok_sekarang_ifi,
                    'stok_sekarang_ap'                          => $model->stok_sekarang_ap,
                    'stok_sekarang_ifg'                         => $model->stok_sekarang_ifg,
                    'stok_keluar_medis_'.$this->periode.'_hari' => $model->{'stok_keluar_medis_'.$this->periode.'_hari'},
                    'ke_pasien_'.$this->periode.'_hari'         => $model->{'ke_pasien_'.$this->periode.'_hari'},
                    'piutang_'.$this->periode.'_hari'           => $model->{'piutang_'.$this->periode.'_hari'},
                    'total_stok_sekarang'                       => $model->stok_sekarang_ifi + $model->stok_sekarang_ap + $model->stok_sekarang_ifg,
                    'total_keluar_'.$this->periode.'_hari'      => $model->{'stok_keluar_medis_'.$this->periode.'_hari'} + $model->{'ke_pasien_'.$this->periode.'_hari'} + $model->{'piutang_'.$this->periode.'_hari'},
                    'saran_order'                               => $model->saran_order,
                    'nama_industri'                             => $model->nama_industri,
                    'harga_beli'                                => $model->harga_beli,
                    'harga_beli_total'                          => $model->harga_beli_total,
                    'harga_beli_terakhir'                       => $model->harga_beli_terakhir,
                    'diskon_terakhir'                           => $model->diskon_terakhir,
                    'supplier_terakhir'                         => $model->supplier_terakhir,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Nama',
            'Satuan kecil',
            'Kategori',
            'Stok Minimal',
            'Stok Farmasi RWI',
            'Stok Farmasi B',
            'Stok Farmasi IGD',
            "Stok Keluar Medis ({$this->periode} Hari)",
            "Ke Pasien ({$this->periode} Hari)",
            "Piutang ({$this->periode} Hari)",
            'Total Stok Sekarang',
            "Total Keluar ({$this->periode} Hari)",
            'Saran Order',
            'Supplier',
            'Harga per Unit (Rp)',
            'Total Harga (Rp)',
            'Harga Beli Terakhir (Rp)',
            'Diskon Terakhir (%)',
            'Supplier Terakhir',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Rencana Order Farmasi',
            'Per '.now()->translatedFormat('d F Y'),
        ];
    }
}
