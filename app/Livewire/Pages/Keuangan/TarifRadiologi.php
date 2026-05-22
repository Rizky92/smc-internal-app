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
use Illuminate\Support\Str;
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
                    Str::transliterate($model->kd_jenis_prw),
                    Str::transliterate($model->nm_perawatan),
                    Str::transliterate($model->bagian_rs),
                    Str::transliterate($model->bhp),
                    Str::transliterate($model->tarif_perujuk),
                    Str::transliterate($model->tarif_tindakan_dokter),
                    Str::transliterate($model->tarif_tindakan_petugas),
                    Str::transliterate($model->kso),
                    Str::transliterate($model->menejemen),
                    Str::transliterate($model->total_byr),
                    Str::transliterate($model->kd_pj),
                    Str::transliterate($model->png_jawab),
                    Str::transliterate($model->kelas),
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
            'Kode Jenis Bayar',
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
