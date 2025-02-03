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
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class PemakaianStokFarmasi extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    public function mount(): void
    {
        $this->defaultValues();
    }

    /**
     * @return Paginator|array<empty, empty>
     */
    public function getPemakaianStokObatProperty()
    {
        return $this->isDeferred ? [] : Obat::query()
            ->pemakaianStok()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.pemakaian-stok-farmasi')
            ->layout(BaseLayout::class, ['title' => 'Pemakaian Stok Farmasi']);
    }

    protected function defaultValues(): void
    {
        //
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => Obat::query()->pemakaianStok()
                ->cursor()
                ->map(fn (Obat $model, $_): array => [
                    'kode_brng'          => $model->kode_brng,
                    'nama_brng'          => $model->nama_brng,
                    'satuan_kecil'       => $model->satuan_kecil,
                    'stok_saat_ini'      => $model->stok_saat_ini,
                    'kategori'           => $model->kategori,
                    'ke_pasien_14_hari'  => $model->ke_pasien_14_hari,
                    'pemakaian_1_minggu' => $model->pemakaian_1_minggu,
                    'pemakaian_1_bulan'  => $model->pemakaian_1_bulan,
                    'pemakaian_3_bulan'  => $model->pemakaian_3_bulan,
                    'pemakaian_6_bulan'  => $model->pemakaian_6_bulan,
                    'pemakaian_10_bulan' => $model->pemakaian_10_bulan,
                    'pemakaian_12_bulan' => $model->pemakaian_12_bulan,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode',
            'Nama',
            'Satuan kecil',
            'Kategori',
            'Stok Seluruh Depo Farmasi Saat Ini',
            'Ke Pasien (14 Hari)',
            'Pemakaian 1 Minggu',
            'Pemakaian 1 Bulan',
            'Pemakaian 3 Bulan',
            'Pemakaian 6 Bulan',
            'Pemakaian 10 Bulan',
            'Pemakaian 12 Bulan',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Pemakaian Stok Farmasi',
            'Per '.now()->translatedFormat('d F Y'),
        ];
    }
}
