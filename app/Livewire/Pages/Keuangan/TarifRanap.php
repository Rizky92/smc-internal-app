<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\JenisPerawatanRanap;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class TarifRanap extends Component
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
        return $this->isDeferred ? [] : JenisPerawatanRanap::query()
            ->tarifRanap()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.tarif-ranap')
            ->layout(BaseLayout::class, ['title' => 'Tarif Ranap']);
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => JenisPerawatanRanap::query()
                ->tarifRanap()
                ->search($this->cari)
                ->cursor()
                ->map(fn (JenisPerawatanRanap $model): array => [
                    $model->kd_jenis_prw,
                    $model->nm_perawatan,
                    $model->nm_kategori,
                    $model->material,
                    $model->bhp,
                    $model->tarif_tindakandr,
                    $model->tarif_tindakanpr,
                    $model->kso,
                    $model->menejemen,
                    $model->total_byrdr,
                    $model->total_byrpr,
                    $model->total_byrdrpr,
                    $model->png_jawab,
                    $model->nm_bangsal,
                    $model->kelas,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Tindakan',
            'Nama Tindakan',
            'Kategori',
            'Jasa Sarana',
            'BHP/Paket Obat',
            'Jasa Medis Dr',
            'Jasa Medis PR',
            'KSO',
            'Menejemen',
            'Total Bayar DR',
            'Total Bayar PR',
            'Total Bayar DR & PR',
            'Jenis Bayar',
            'Nama Bangsal',
            'Kelas',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Tarif Rawat Inap',
        ];
    }

    protected function defaultValues(): void
    {
        //
    }
}
