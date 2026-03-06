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
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Tindakan',
            'Nama Tindakan',
            'Bagian RS',
            'BHP/Paket Obat',
            'Tarif Perujuk',
            'Tarif Tindakan Dokter',
            'Tarif Tindakan Petugas',
            'KSO',
            'Menejemen',
            'Total Bayar',
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
