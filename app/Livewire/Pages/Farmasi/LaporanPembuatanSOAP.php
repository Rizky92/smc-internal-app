<?php

namespace App\Livewire\Pages\Farmasi;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Perawatan\PemeriksaanRanap;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LaporanPembuatanSOAP extends Component
{
    use DeferredLoading;
    use ExcelExportable;
    use Filterable;
    use FlashComponent;
    use LiveTable;
    use MenuTracker;

    /** @var ?string */
    public $tglAwal;

    /** @var ?string */
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

    /**
     * @return array<empty, empty>|Paginator
     */
    public function getDataLaporanPembuatanSOAPProperty()
    {
        return $this->isDeferred ? [] : PemeriksaanRanap::query()
            ->pemeriksaanOlehFarmasi($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns)
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.farmasi.laporan-pembuatan-soap')
            ->layout(BaseLayout::class, ['title' => 'Laporan Pembuatan SOAP Farmasi/Visite Apoteker']);
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
            fn () => PemeriksaanRanap::query()
                ->pemeriksaanOlehFarmasi($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (PemeriksaanRanap $model): array => [
                    'tgl_perawatan' => $model->tgl_perawatan,
                    'jam_rawat'     => $model->jam_rawat,
                    'no_rawat'      => $model->no_rawat,
                    'nm_pasien'     => $model->nm_pasien,
                    'png_jawab'     => $model->png_jawab,
                    'dpjp'          => optional($model->dpjp)->pluck('nm_dokter')->join('; '),
                    'alergi'        => $model->alergi,
                    'keluhan'       => $model->keluhan,
                    'pemeriksaan'   => $model->pemeriksaan,
                    'penilaian'     => $model->penilaian,
                    'rtl'           => $model->rtl,
                    'nip'           => $model->nip.' '.$model->nama,
                    'nm_jbtn'       => $model->nm_jbtn,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tgl. SOAP',
            'Jam',
            'No. Rawat',
            'Pasien',
            'Jenis Bayar',
            'DPJP',
            'Alergi',
            'Keluhan (Subjek)',
            'Pemeriksaan (Objek)',
            'Penilaian (Asesmen)',
            'RTL (Plan)',
            'Petugas',
            'Jabatan',
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
            'Laporan Pembuatan SOAP oleh Farmasi',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
