<?php

namespace App\Livewire\Pages\Keuangan;

use App\Livewire\Concerns\DeferredLoading;
use App\Livewire\Concerns\ExcelExportable;
use App\Livewire\Concerns\Filterable;
use App\Livewire\Concerns\FlashComponent;
use App\Livewire\Concerns\LiveTable;
use App\Livewire\Concerns\MenuTracker;
use App\Models\Radiologi\HasilPeriksaRadiologi;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LaporanTindakanRadiologi extends Component
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

    public function getDataLaporanTindakanRadiologiProperty()
    {
        return $this->isDeferred ? [] : HasilPeriksaRadiologi::query()
            ->laporanTindakanRadiologi($this->tglAwal, $this->tglAkhir)
            ->search($this->cari)
            ->sortWithColumns($this->sortColumns, [
                'periksa_radiologi.tgl_periksa' => 'asc',
                'periksa_radiologi.jam'         => 'asc',
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.pages.keuangan.laporan-tindakan-radiologi')
            ->layout(BaseLayout::class, ['title' => 'Laporan Jumlah Tindakan Radiologi']);
    }

    protected function defaultValues(): void
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            HasilPeriksaRadiologi::query()
                ->laporanTindakanRadiologi($this->tglAwal, $this->tglAkhir)
                ->get()
                ->map(fn (HasilPeriksaRadiologi $model): array => [
                    'no_rawat'          => $model->no_rawat,
                    'no_rkm_medis'      => $model->no_rkm_medis,
                    'nm_pasien'         => $model->nm_pasien,
                    'png_jawab'         => $model->png_jawab,
                    'nama_petugas'      => $model->nama_petugas,
                    'tgl_periksa'       => $model->tgl_periksa,
                    'jam'               => $model->jam,
                    'dokter_perujuk'    => $model->dokter_perujuk,
                    'kd_jenis_prw'      => $model->kd_jenis_prw,
                    'nm_perawatan'      => $model->nm_perawatan,
                    'biaya'             => floatval($model->biaya),
                    'status_bayar'      => $model->status_bayar,
                    'status'            => $model->status,
                    'kd_dokter'         => $model->kd_dokter,
                    'nm_dokter'         => $model->nm_dokter,
                    'hasil_pemeriksaan' => $model->hasil_pemeriksaan,
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
            'Biaya (Rp)',
            'Status Bayar',
            'Jenis Perawatan',
            'Kode Dokter',
            'Nama Dokter Pemeriksa',
            'Hasil Pemeriksaan',
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
            'Laporan Jumlah Tindakan Radiologi',
            now()->translatedFormat('d F Y'),
            $periode,
        ];
    }
}
