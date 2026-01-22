<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Keuangan\JenisPerawatan;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class TarifRalan extends Component
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
        return $this->isDeferred ? [] : JenisPerawatan::query()
            ->tarifRalan()
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.tarif-ralan')
            ->layout(BaseLayout::class, ['title' => 'Tarif Ralan']);
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => JenisPerawatan::query()
                ->tarifRalan()
                ->search($this->cari)
                ->cursor()
                ->map(fn (JenisPerawatan $model): array => [
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
                    $model->nm_poli,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Kode Jenis Perawatan',
            'Nama Tarif',
            'Kategori',
            'Material',
            'BHP',
            'Tarif Tindakan DR',
            'Tarif Tindakan PR',
            'KSO',
            'Menejemen',
            'Total Bayar DR',
            'Total Bayar PR',
            'Total Bayar DRPR',
            'Kode Penanggung Jawab',
            'Poli',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Tarif Rawat Jalan',
        ];
    }

    protected function defaultValues(): void
    {
        //
    }
}
