<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\RegistrasiPasien;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class IGDKeRawatInap extends Component
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
        return $this->isDeferred ? [] : RegistrasiPasien::query()
            ->igdKeRawatInap($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, [
                'tgl_registrasi' => 'asc',
                'jam_registrasi' => 'asc',
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.i-g-d-ke-rawat-inap')
            ->layout(BaseLayout::class, ['title' => 'IGD Ke Rawat Inap']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            fn () => RegistrasiPasien::query()
            ->igdKeRawatInap($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->cursor()
            ->map(fn(RegistrasiPasien $model): array => [
                'no_rawat'          => $model->no_rawat,
                'tgl_registrasi'    => $model->tgl_registrasi,
                'jam_reg'           => $model->jam_reg,
                'no_rkm_medis'      => $model->no_rkm_medis,
                'nm_pasien'         => $model->nm_pasien,
                'dpjp_igd'          => $model->dpjp_igd,
                'dpjp_ranap'        => $model->dpjp_ranap,
            ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'Tgl. Registrasi',
            'Jam Registrasi',
            'No. RM',
            'Nama Pasien',
            'DPJP IGD',
            'DPJP Ranap'
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
            'Laporan Pasien Masuk IGD Melalui Rawat Inap',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
