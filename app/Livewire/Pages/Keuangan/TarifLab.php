<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\JenisPerawatanLab;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class TarifLab extends Component
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
        return $this->isDeferred ? [] : JenisPerawatanLab::query()
            ->tarifLab()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.tarif-lab')
            ->layout(BaseLayout::class, ['title' => 'Tarif Lab']);
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => JenisPerawatanLab::query()
                ->tarifLab()
                ->search($this->cari)
                ->cursor()
                ->map(fn (JenisPerawatanLab $model): array => [
                    $model->kd_jenis_prw,
                    $model->nm_perawatan,
                    $model->bagian_rs,
                    $model->bhp,
                    $model->tarif_perujuk,
                    $model->tarif_tindakan_dokter,
                    $model->tarif_tindakan_petugas,
                    $model->kso,
                    $model->menejemen,
                    $model->total_byr,
                    $model->png_jawab,
                    $model->kelas,
                    $model->kategori,
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
            'Kategori',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Tarif Laboratorium',
        ];
    }

    protected function defaultValues(): void
    {
        //
    }
}
