<?php

namespace App\Livewire\Pages\Admission;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Admission\AntreanOnsite as AntreanOnsiteModel;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class AntreanOnsite extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal'  => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getCollectionProperty()
    {
        return $this->isDeferred ? [] : AntreanOnsiteModel::query()
            ->perminataanNomorAntreanOnsite($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.admission.antrean-onsite')
            ->layout(BaseLayout::class, ['title' => 'Antrean Onsite']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => AntreanOnsiteModel::query()
                ->perminataanNomorAntreanOnsite($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns)
                ->cursor()
                ->map(fn (AntreanOnsiteModel $model): array => [
                    'nomor'        => $model->nomor,
                    'tanggal'      => $model->tanggal,
                    'jam'          => $model->jam,
                    'jam_panggil'  => $model->jam_panggil,
                    'no_rawat'     => $model->no_rawat,
                    'no_rkm_medis' => $model->no_rkm_medis,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Nomor',
            'Tanggal',
            'Jam',
            'Jam Panggil',
            'No. Rawat',
            'No. RM',
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode '.$periodeAwal->translatedFormat('d F Y').' s.d. '.$periodeAkhir->translatedFormat('d F Y');

        if ($periodeAwal->isSameDay($periodeAkhir)) {
            $periode = $periodeAwal->translatedFormat('d F Y');
        }

        return [
            'RS Samarinda Medika Citra',
            'Data Antrean Onsite',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
