<?php

namespace App\Livewire\Pages\Casemix;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Casemix\DataTriaseIgd;
use App\View\Components\BaseLayout;
use Illuminate\View\View;
use Livewire\Component;

class LaporanTriaseIgdZonaHijau extends Component
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
        return $this->isDeferred ? [] : DataTriaseIgd::query()
            ->triaseIgdZonaHijau($this->tglAwal, $this->tglAkhir)
            ->sortWithColumns($this->sortColumns)
            ->search($this->cari)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.casemix.laporan-triase-igd-zona-hijau')
            ->layout(BaseLayout::class, ['title' => 'Laporan Triase IGD Zona Hijau All Jaminan']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => DataTriaseIgd::query()
                ->triaseIgdZonaHijau($this->tglAwal, $this->tglAkhir)
                ->sortWithColumns($this->sortColumns)
                ->search($this->cari)
                ->cursor()
                ->map(fn (DataTriaseIgd $model): array => [
                    'No. Rawat'         => $model->no_rawat,
                    'No. SEP'           => $model->no_sep,
                    'No. RM'            => $model->no_rkm_medis,
                    'Nama Pasien'       => $model->nm_pasien,
                    'Jenis Bayar'       => $model->png_jawab,
                    'Tgl. Kunjungan'    => $model->tgl_kunjungan,
                    'Cara Masuk'        => $model->cara_masuk,
                    'Status'            => $model->stts,
                    'Jenis Rawat'       => $model->status_lanjut,
                    'Alasan Kedatangan' => $model->alasan_kedatangan,
                    'Macam Kasus'       => $model->macam_kasus,
                    'Zona'              => $model->plan,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. SEP',
            'No. RM',
            'Nama Pasien',
            'Jenis Bayar',
            'Tgl. Kunjungan',
            'Cara Masuk',
            'Status',
            'Jenis Rawat',
            'Alasan Kedatangan',
            'Macam Kasus',
            'Zona',
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
            'Laporan Triase IGD Zona Hijau',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
