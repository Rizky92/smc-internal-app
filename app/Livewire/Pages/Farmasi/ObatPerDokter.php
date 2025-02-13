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
use Illuminate\View\View;
use Livewire\Component;

class ObatPerDokter extends Component
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
            'tglAwal'  => ['except' => now()->startOfMonth()->toDateString(), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->toDateString(), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getObatPerDokterProperty()
    {
        return $this->isDeferred ? [] : ResepObat::query()
            ->penggunaanObatPerDokter($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.obat-per-dokter')
            ->layout(BaseLayout::class, ['title' => 'Penggunaan Obat Per Dokter Peresep']);
    }

    protected function defaultValues(): void
    {
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
                ->penggunaanObatPerDokter($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (ResepObat $model): array => [
                    'no_resep'      => $model->no_resep,
                    'tgl_perawatan' => $model->tgl_perawatan,
                    'jam'           => $model->jam,
                    'no_rawat'      => $model->no_rawat,
                    'nama_brng'     => $model->nama_brng,
                    'nama'          => $model->nama,
                    'jml'           => floatval($model->jml),
                    'nm_dokter'     => $model->nm_dokter,
                    'dpjp'          => $model->dpjp,
                    'status'        => str()->title($model->status),
                    'nm_poli'       => $model->nm_poli,
                    'png_jawab'     => $model->png_jawab,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. Resep',
            'Tgl. Validasi',
            'Jam',
            'Nama Obat',
            'Kategori',
            'Jumlah',
            'Dokter Peresep',
            'DPJP',
            'Jenis Perawatan',
            'Asal Poli',
            'Jenis Bayar',
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
            'Laporan Penggunaan Obat Per Dokter Peresep',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
