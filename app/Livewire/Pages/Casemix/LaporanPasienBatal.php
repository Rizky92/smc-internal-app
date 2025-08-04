<?php

namespace App\Livewire\Pages\Casemix;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Casemix\BridgingSep;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPasienBatal extends Component
{
    use FlashComponent;
    use Filterable;
    use ExcelExportable;
    use LiveTable;
    use MenuTracker;
    use DeferredLoading;

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
        return $this->isDeferred ? [] : BridgingSep::query()
            ->pasienBatal($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.casemix.laporan-pasien-batal')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pasien Batal']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn() => BridgingSep::query()
                ->pasienBatal($this->tglAwal, $this->tglAkhir)
                ->search($this->cari)
                ->sortWithColumns($this->sortColumns)
                ->cursor()
                ->map(fn (BridgingSep $model): array => [
                    'No. SEP'               => $model->no_sep,
                    'No. Rawat'             => $model->no_rawat,
                    'Tgl. SEP'              => $model->tglsep,
                    'Jenis Pelayanan'       => $model->jenis_pelayanan,
                    'Status Lanjut'         => $model->status_lanjut,
                    'No. RM'                => $model->no_rm,
                    'Nama Pasien'           => $model->nama_pasien,
                    'Status Bayar'          => $model->status_bayar,
                    'Status Periksa'        => $model->status_periksa,
                    'Jaminan Registrasi'    => $model->jaminan_registrasi,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. SEP',
            'No. Rawat',
            'Tgl. SEP',
            'Jenis Pelayanan',
            'Status Lanjut',
            'No. RM',
            'Nama Pasien',
            'Status Bayar',
            'Status Periksa',
            'Jaminan Registrasi',
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
            'Laporan Data SEP BPJS Pasien Batal',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}

