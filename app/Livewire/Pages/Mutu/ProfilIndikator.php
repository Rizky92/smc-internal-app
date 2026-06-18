<?php

namespace App\Livewire\Pages\Mutu;

use App\Application\Quality\Actions\GetQualityIndicatorProfileListAction;
use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Component;

class ProfilIndikator extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    protected $listeners = [
        'profile-saved' => '$refresh',
    ];

    public function getCollectionProperty(): LengthAwarePaginator
    {
        return app(GetQualityIndicatorProfileListAction::class)->execute([
            'search' => $this->cari,
        ], $this->perpage);
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function render(): View
    {
        return view('livewire.pages.mutu.profil-indikator', [
            'profiles' => $this->isDeferred ? [] : $this->collection,
        ])
            ->layout(BaseLayout::class, ['title' => 'Profil Indikator Mutu']);
    }

    protected function defaultValues(): void
    {
        //
    }

    protected function dataPerSheet(): array
    {
        return [
            'Profil Indikator' => fn () => $this->collection->map(fn ($profile) => [
                $profile->id,
                $profile->title,
                $profile->category->name ?? '-',
                $profile->standard,
                $profile->frequency,
                $profile->dimension,
                $profile->rationale,
                $profile->indicator_type,
                $profile->objective,
                $profile->definition,
                $profile->measurement_unit,
                $profile->formula,
                $profile->numerator,
                $profile->denominator,
                $profile->data_collection_method,
                $profile->instrument,
                $profile->sample_size,
                $profile->sampling_method,
                $profile->data_presentation,
                $profile->analysis_period,
            ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'ID', 'Judul Indikator', 'Kategori', 'Standar', 'Frekuensi',
            'Dimensi Mutu', 'Dasar Pemikiran', 'Tipe Indikator', 'Tujuan',
            'Definisi Operasional', 'Satuan Pengukuran', 'Formula',
            'Numerator', 'Denominator', 'Metode Pengumpulan Data',
            'Instrumen', 'Besar Sampel', 'Cara Pengambilan Sampel',
            'Penyajian Data', 'Periode Analisis',
        ];
    }

    protected function pageHeaders(): array
    {
        return ['PROFIL INDIKATOR MUTU', 'Tanggal Cetak: '.now()->format('d-m-Y H:i:s')];
    }
}
