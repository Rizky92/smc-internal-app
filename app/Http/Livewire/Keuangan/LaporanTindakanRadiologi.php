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
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanTindakanRadiologi extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

    public $periodeAwal;

    public $periodeAkhir;

    protected function queryString()
    {
        return [
            'periodeAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'periodeAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataLaporanTindakanRadiologiProperty()
    {
        return HasilPeriksaRadiologi::query()
            ->laporanTindakanRadiologi($this->periodeAwal, $this->periodeAkhir)
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
                'periksa_radiologi.jam' => 'asc',
            ])
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.keuangan.laporan-tindakan-radiologi')
            ->layout(BaseLayout::class, ['title' => 'Laporan Jumlah Tindakan Radiologi']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            HasilPeriksaRadiologi::laporanTindakanRadiologi($this->periodeAwal, $this->periodeAkhir)->get(),
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
            now()->format('d F Y'),
            CarbonImmutable::parse($this->periodeAwal)->format('d F Y') . ' - ' . CarbonImmutable::parse($this->periodeAkhir)->format('d F Y'),
        ];
    }
}