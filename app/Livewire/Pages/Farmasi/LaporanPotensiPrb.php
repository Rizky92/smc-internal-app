<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Casemix\BpjsPrb;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPotensiPrb extends Component
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
        return $this->isDeferred ? [] : BpjsPrb::query()
            ->laporanPotensiPrb($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-potensi-prb')
            ->layout(BaseLayout::class, ['title' => 'Laporan Potensi PRB BPJS']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => BpjsPrb::query()
                ->laporanPotensiPrb($this->tglAwal, $this->tglAkhir)
                ->sortWithColumns($this->sortColumns)
                ->search($this->cari)
                ->cursor()
                ->map(fn (BpjsPrb $model): array => [
                    'No. SEP'           => $model->no_sep,
                    'Tgl. SEP'          => $model->tglsep,
                    'No. Rawat'         => $model->no_rawat,
                    'No. RM'            => $model->nomr,
                    'No. Kartu'         => $model->no_kartu,
                    'Nama Pasien'       => $model->nama_pasien,
                    'Poli Tujuan'       => $model->nmpolitujuan,
                    'DPJP'              => $model->nmdpdjp,
                    'Jns. Pelayanan'    => $model->jnspelayanan == '1' ? 'Ranap' : 'Ralan',
                    'Diagnosa'          => $model->nmdiagnosaawal,
                    'Peserta'           => $model->peserta,
                    'Asal Rujukan'      => $model->asal_rujukan,
                    'No. Rujukan'       => $model->no_rujukan,
                    'Tgl. Rujukan'      => $model->tglrujukan,
                    'No. SKDP'          => $model->noskdp,
                    'PRB'               => $model->prb,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. SEP',
            'Tgl. SEP',
            'No. Rawat',
            'No. RM',
            'No. Kartu',
            'Nama Pasien',
            'Poli Tujuan',
            'DPJP',
            'Jns. Pelayanan',
            'Diagnosa',
            'Peserta',
            'Asal Rujukan',
            'No. Rujukan',
            'Tgl. Rujukan',
            'No. SKDP',
            'PRB',
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
            'Laporan Potensi PRB BPJS',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
