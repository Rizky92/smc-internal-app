<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Laboratorium\PeriksaLab;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LaporanTindakanLab extends Component
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

    public function getDataLaporanTindakanLabProperty(): Paginator
    {
        return PeriksaLab::query()
            ->laporanTindakanLab($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns(
                $this->sortColumns,
                ['nama_petugas' => 'petugas.nama'],
                [
                    'periksa_lab.tgl_periksa' => 'asc',
                    'periksa_lab.jam'         => 'asc',
                ]
            )
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-tindakan-lab')
            ->layout(BaseLayout::class, ['title' => 'Laporan Jumlah Tindakan Laboratorium']);
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
            fn () => PeriksaLab::query()
                ->laporanTindakanLab($this->tglAwal, $this->tglAkhir)
                ->cursor()
                ->map(fn (PeriksaLab $model): array => [
                    'no_rawat'       => $model->no_rawat,
                    'no_rkm_medis'   => $model->no_rkm_medis,
                    'nm_pasien'      => $model->nm_pasien,
                    'png_jawab'      => $model->png_jawab,
                    'nama_petugas'   => $model->nama_petugas,
                    'tgl_periksa'    => $model->tgl_periksa,
                    'jam'            => $model->jam,
                    'dokter_perujuk' => $model->dokter_perujuk,
                    'kd_jenis_prw'   => $model->kd_jenis_prw,
                    'nm_perawatan'   => $model->nm_perawatan,
                    'kategori'       => $model->kategori,
                    'biaya'          => floatval($model->biaya),
                    'status_bayar'   => $model->status_bayar,
                    'status'         => $model->status,
                    'kd_dokter'      => $model->kd_dokter,
                    'nm_dokter'      => $model->nm_dokter,
                ]),
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'No. Rawat',
            'No. RM',
            'Pasien',
            'Jenis Bayar',
            'Petugas',
            'Tgl. Periksa',
            'Jam',
            'Perujuk',
            'Kode Tindakan',
            'Nama Tindakan',
            'Kategori',
            'Biaya (Rp)',
            'Status Bayar',
            'Jenis Perawatan',
            'Kode Dokter',
            'Nama Dokter Pemeriksa',
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
            'Laporan Jumlah Tindakan Laboratorium',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
