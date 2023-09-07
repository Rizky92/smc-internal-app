<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Laboratorium\HasilPeriksaLab;
use App\Support\Livewire\Concerns\DeferredLoading;
use App\Support\Livewire\Concerns\ExcelExportable;
use App\Support\Livewire\Concerns\Filterable;
use App\Support\Livewire\Concerns\FlashComponent;
use App\Support\Livewire\Concerns\LiveTable;
use App\Support\Livewire\Concerns\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\View\View;
use Livewire\Component;

class LaporanTindakanLab extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    /** @var string */
    public $tglAwal;

    /** @var string */
    public $tglAkhir;

    protected function queryString(): array
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount(): void
    {
        $this->defaultValues();
    }

    public function getDataLaporanTindakanLabProperty(): Paginator
    {
        return HasilPeriksaLab::query()
            ->laporanTindakanLab($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'periksa_lab.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'penjab.png_jawab',
                'petugas.nama',
                'periksa_lab.dokter_perujuk',
                'jns_perawatan_lab.kd_jenis_prw',
                'jns_perawatan_lab.nm_perawatan',
                'periksa_lab.kategori',
                'reg_periksa.status_bayar',
                'periksa_lab.status',
                'periksa_lab.kd_dokter',
                'dokter.nm_dokter',
            ])
            ->sortWithColumns(
                $this->sortColumns,
                ['nama_petugas' => 'petugas.nama'],
                [
                    'periksa_lab.tgl_periksa' => 'asc',
                    'periksa_lab.jam' => 'asc',
                ]
            )
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.keuangan.laporan-tindakan-lab')
            ->layout(BaseLayout::class, ['title' => 'Laporan Jumlah Tindakan Laboratorium']);
    }

    protected function defaultValues(): void
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            HasilPeriksaLab::query()
                ->laporanTindakanLab($this->tglAwal, $this->tglAkhir)
                ->get()
                ->map(fn (HasilPeriksaLab $model): array => [
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
            "No. Rawat",
            "No. RM",
            "Pasien",
            "Jenis Bayar",
            "Petugas",
            "Tgl. Periksa",
            "Jam",
            "Perujuk",
            "Kode Tindakan",
            "Nama Tindakan",
            "Kategori",
            "Biaya (Rp)",
            "Status Bayar",
            "Jenis Perawatan",
            "Kode Dokter",
            "Nama Dokter Pemeriksa",
        ];
    }

    protected function pageHeaders(): array
    {
        $periodeAwal = carbon($this->tglAwal);
        $periodeAkhir = carbon($this->tglAkhir);

        $periode = 'Periode ' . $periodeAwal->translatedFormat('d F Y') . ' s.d. ' . $periodeAkhir->translatedFormat('d F Y');

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
