<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Farmasi\ResepObat;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class KunjunganPerPoli extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /* @var string */
    public $statusLanjut;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'statusLanjut' => ['except' => 'semua', 'as' => 'status_lanjut'],
            'tglAwal'      => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir'     => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataKunjunganPerPoliProperty()
    {
        return $this->isDeferred ? [] : ResepObat::query()
            ->kunjunganPerPoli($this->tglAwal, $this->tglAkhir, $this->statusLanjut)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.kunjungan-per-poli')
            ->layout(BaseLayout::class, ['title' => 'Kunjungan Resep Pasien Per Poli']);
    }

    protected function defaultValues(): void
    {
        $this->statusLanjut = 'semua';
        $this->tglAwal = now()->startOfMonth()->toDateString();
        $this->tglAkhir = now()->endOfMonth()->toDateString();
    }

    /**
     * @psalm-return array{0: mixed}
     */
    protected function dataPerSheet(): array
    {
        return [
            fn () => ResepObat::query()
                ->kunjunganPerPoli($this->tglAwal, $this->tglAkhir, $this->statusLanjut)
                ->cursor()
                ->map(fn (ResepObat $model): array => [
                    'no_rawat'          => Str::transliterate($model->no_rawat ?? ''),
                    'no_resep'          => Str::transliterate($model->no_resep ?? ''),
                    'nm_pasien'         => Str::transliterate($model->nm_pasien ?? ''),
                    'umur'              => Str::transliterate($model->umur ?? ''),
                    'tgl_perawatan'     => Str::transliterate($model->tgl_perawatan ?? ''),
                    'jam'               => Str::transliterate($model->jam ?? ''),
                    'nm_dokter_peresep' => Str::transliterate($model->nm_dokter_peresep ?? ''),
                    'nm_dokter_poli'    => Str::transliterate($model->nm_dokter_poli ?? ''),
                    'status_lanjut'     => Str::transliterate($model->status_lanjut ?? ''),
                    'nm_poli'           => Str::transliterate($model->nm_poli ?? ''),
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. Resep',
            'Pasien',
            'Umur',
            'Tgl. Validasi',
            'Jam',
            'Dokter Peresep',
            'Dokter Poli',
            'Jenis Perawatan',
            'Asal Poli',
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
            'Laporan Kunjungan Pasien Per Poli di Farmasi',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
