<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Radiologi\HasilPeriksaRadiologi;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LaporanTindakanRadiologi extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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

    public function getDataLaporanTindakanRadiologiProperty(): Paginator
    {
        return HasilPeriksaRadiologi::query()
            ->laporanTindakanRadiologi($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'periksa_radiologi.no_rawat',
                'reg_periksa.no_rkm_medis',
                'pasien.nm_pasien',
                'penjab.png_jawab',
                'petugas.nama',
                'periksa_radiologi.dokter_perujuk',
                'jns_perawatan_radiologi.kd_jenis_prw',
                'jns_perawatan_radiologi.nm_perawatan',
                'reg_periksa.status_bayar',
                'periksa_radiologi.status',
                'periksa_radiologi.kd_dokter',
                'dokter.nm_dokter',
                'hasil_radiologi.hasil',
            ])
            ->sortWithColumns($this->sortColumns, [
                'no_rawat'          => 'periksa_radiologi.no_rawat',
                'no_rkm_medis'      => 'reg_periksa.no_rkm_medis',
                'nm_pasien'         => 'pasien.nm_pasien',
                'png_jawab'         => 'penjab.png_jawab',
                'nama_petugas'      => 'petugas.nama',
                'tgl_periksa'       => 'periksa_radiologi.tgl_periksa',
                'jam'               => 'periksa_radiologi.jam',
                'dokter_perujuk'    => 'periksa_radiologi.dokter_perujuk',
                'kd_jenis_prw'      => 'jns_perawatan_radiologi.kd_jenis_prw',
                'nm_perawatan'      => 'jns_perawatan_radiologi.nm_perawatan',
                'biaya'             => 'periksa_radiologi.biaya',
                'status_bayar'      => 'reg_periksa.status_bayar',
                'status'            => 'periksa_radiologi.status',
                'kd_dokter'         => 'periksa_radiologi.kd_dokter',
                'nm_dokter'         => 'dokter.nm_dokter',
                'hasil_pemeriksaan' => DB::raw('LEFT(hasil_radiologi.hasil, 200)'),
            ], [
                'periksa_radiologi.tgl_periksa' => 'asc',
                'periksa_radiologi.jam'         => 'asc',
            ])
            ->paginate($this->perpage);
    }

    public function render(): View
    {
        return view('livewire.keuangan.laporan-tindakan-radiologi')
            ->layout(BaseLayout::class, ['title' => 'Laporan Jumlah Tindakan Radiologi']);
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
            "Biaya (Rp)",
            "Status Bayar",
            "Jenis Perawatan",
            "Kode Dokter",
            "Nama Dokter Pemeriksa",
            "Hasil Pemeriksaan",
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Jumlah Tindakan Radiologi',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->format('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->format('d F Y'),
        ];
    }
}
