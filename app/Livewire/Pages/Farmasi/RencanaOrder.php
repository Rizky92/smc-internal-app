<?php

namespace App\Livewire\Pages\Farmasi;

use App\Models\Farmasi\Obat;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class RencanaOrder extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return \Illuminate\Contracts\Pagination\Paginator|array<empty, empty>
     */
    public function getStokDaruratObatProperty()
    {
        return $this->isDeferred
            ? []
            : Obat::query()
                ->daruratStok()
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
        //
    }

    protected function dataPerSheet(): array
    {
        return [
            Obat::query()
                ->daruratStok()
                ->get()
                ->map(fn (Obat $model, $_): array => [
                    'nama_brng'           => $model->nama_brng,
                    'satuan_kecil'        => $model->satuan_kecil,
                    'kategori'            => $model->kategori,
                    'stok_minimal'        => $model->stokminimal,
                    'stok_sekarang_ifi'   => $model->stok_sekarang_ifi,
                    'stok_sekarang_ap'    => $model->stok_sekarang_ap,
                    'saran_order'         => $model->saran_order,
                    'nama_industri'       => $model->nama_industri,
                    'harga_beli'          => $model->harga_beli,
                    'harga_beli_total'    => $model->harga_beli_total,
                    'harga_beli_terakhir' => $model->harga_beli_terakhir,
                    'diskon_terakhir'     => $model->diskon_terakhir,
                    'supplier_terakhir'   => $model->supplier_terakhir,
                    'ke_pasien_14_hari'   => $model->ke_pasien_14_hari,
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
            'Saran Order',
            'Supplier',
            'Harga per Unit (Rp)',
            'Total Harga (Rp)',
            'Harga Beli Terakhir (Rp)',
            'Diskon Terakhir (%)',
            'Supplier Terakhir',
            'Ke Pasien (14 Hari)',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Rencana Order Farmasi',
            'Per ' . now()->translatedFormat('d F Y'),
        ];
    }
}
