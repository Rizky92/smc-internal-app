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

class LaporanPasienCob extends Component
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
            ->registrasiCob($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.casemix.laporan-pasien-cob')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pasien Cob']);
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
                ->registrasiCob($this->tglAwal, $this->tglAkhir)
                ->sortWithColumns($this->sortColumns)
                ->search($this->cari)
                ->cursor()
                ->map(fn (BridgingSep $model) : array => [
                    'No. SEP'           => $model->no_sep,
                    'Tgl. SEP'          => $model->tglsep,
                    'No. Rawat'         => $model->no_rawat,
                    'Jenis Pelayanan'   => $model->jenis_pelayanan,
                    'No. RM'            => $model->no_rm,
                    'Nama Pasien'       => $model->nama_pasien,
                    'Jenis Bayar'       => $model->jaminan_registrasi,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. SEP',
            'Tgl. SEP',
            'No. Rawat',
            'Jenis Pelayanan',
            'No. RM',
            'Nama Pasien',
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
            'Laporan Data SEP BPJS Untuk Registrasi Non BPJS atau COB',
            now()->translatedFormat('d F Y'),
            $periode
        ];
    }
}
