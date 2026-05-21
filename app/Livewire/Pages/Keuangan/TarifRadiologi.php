<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\JenisPerawatanRadiologi;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class TarifRadiologi extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    protected function queryString(): array
    {
        return [
            //
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : JenisPerawatanRadiologi::query()
            ->tarifRadiologi()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.tarif-radiologi')
            ->layout(BaseLayout::class, ['title' => 'Tarif Radiologi']);
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => JenisPerawatanRadiologi::query()
                ->tarifRadiologi()
                ->search($this->cari)
                ->cursor()
                ->map(fn (JenisPerawatanRadiologi $model): array => [
                    'kd_jenis_prw'              => $model->kd_jenis_prw,
                    'nm_perawatan'              => $model->nm_perawatan,
                    'bagian_rs'                 => $model->bagian_rs,
                    'bhp'                       => $model->bhp,
                    'tarif_perujuk'             => $model->tarif_perujuk,
                    'tarif_tindakan_dokter'     => $model->tarif_tindakan_dokter,
                    'tarif_tindakan_petugas'    => $model->tarif_tindakan_petugas,
                    'kso'                       => $model->kso,
                    'menejemen'                 => $model->menejemen,
                    'total_byr'                 => $model->total_byr,
                    'png_jawab'                 => $model->png_jawab,
                    'kelas'                     => $model->kelas,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Periksa',
            'Nama Pemeriksaan',
            'Jasa Sarana',
            'Paket BHP',
            'Jasa Medis Perujuk',
            'Jasa Medis Dokter',
            'Jasa Medis Petugas',
            'KSO',
            'Menejemen',
            'Total Tarif',
            'Jenis Bayar',
            'Kelas',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Tarif Radiologi',
        ];
    }

    protected function defaultValues(): void
    {
        //
    }
}
