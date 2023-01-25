<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\TambahanBiaya;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanTambahanBiayaPasien extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable;

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

    public function getDataTambahanBiayaPasienProperty()
    {
        return TambahanBiaya::query()
            ->biayaTambahanUntukHonorDokter($this->periodeAwal, $this->periodeAkhir)
            ->search($this->cari, [
                'pasien.nm_pasien',
                'reg_periksa.no_rkm_medis',
                'tambahan_biaya.no_rawat',
                'tambahan_biaya.nama_biaya',
                'penjab.png_jawab',
                'dokter.nm_dokter',
                "coalesce(nullif(trim(dokter_pj.nm_dokter), '-'), '-')",
                'poliklinik.nm_poli',
                'reg_periksa.status_lanjut',
                'reg_periksa.status_bayar',
            ])
            ->sortWithColumns($this->sortColumns, [
                'dokter_ralan' => DB::raw("dokter.nm_dokter"),
                'dokter_ranap' => DB::raw("coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-')"),
            ])
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.keuangan.laporan-tambahan-biaya-pasien')
            ->layout(BaseLayout::class, ['title' => 'Laporan Tambahan Biaya Pasien untuk Honor Dokter']);
    }

    protected function defaultValues()
    {
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            TambahanBiaya::query()
                ->biayaTambahanUntukHonorDokter($this->periodeAwal, $this->periodeAkhir)
                ->get()
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            'Tgl.',
            'Jam',
            'Nama Pasien',
            'No. RM',
            'No. Registrasi',
            'Nama Biaya',
            'Nominal (RP)',
            'Jenis Bayar',
            'Dokter Ralan',
            'Dokter Ranap',
            'Asal Poli',
            'Jenis Perawatan',
            'Status Pembayaran',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Tambahan Biaya Pasien Untuk Honor Dokter',
            Carbon::parse($this->periodeAwal)->format('d F Y') . ' s.d. ' . Carbon::parse($this->periodeAkhir)->format('d F Y'),
        ];
    }
}
