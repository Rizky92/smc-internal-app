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

class ResepBpjsBelumTerbridging extends Component
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
        return $this->isDeferred ? [] : ResepObat::query()
            ->resepBpjsBelumTerbridging($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.resep-bpjs-belum-terbridging')
            ->layout(BaseLayout::class, ['title' => 'Resep BPJS Belum Terbridging']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => ResepObat::query()
                ->resepBpjsBelumTerbridging($this->tglAwal, $this->tglAkhir)
                ->sortWithColumns($this->sortColumns)
                ->search($this->cari)
                ->cursor()
                ->map(fn (ResepObat $model): array => [
                    'No. Resep'     => $model->no_resep,
                    'No. SEP'       => $model->no_sep,
                    'Nama Pasien'   => $model->nama_pasien,
                    'Poliklinik'    => $model->nm_poli,
                    'Kode Obat'     => $model->kode_obat,
                    'Nama Obat'     => $model->nama_obat,
                    'Harga Obat'    => $model->harga_obat,
                    'Tgl. Validasi' => $model->waktu_validasi,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Resep',
            'No. SEP',
            'Nama Pasien',
            'Poliklinik',
            'Kode Obat',
            'Nama Obat',
            'Harga Obat',
            'Tgl. Validasi',
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
            'Resep BPJS Belum Terbridging',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
