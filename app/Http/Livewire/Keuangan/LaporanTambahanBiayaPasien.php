<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\TambahanBiaya;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanTambahanBiayaPasien extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

    public $tglAwal;

    public $tglAkhir;

    protected function queryString()
    {
        return [
            'tglAwal' => ['except' => now()->startOfMonth()->format('Y-m-d'), 'as' => 'tgl_awal'],
            'tglAkhir' => ['except' => now()->endOfMonth()->format('Y-m-d'), 'as' => 'tgl_akhir'],
        ];
    }

    public function mount()
    {
        $this->defaultValues();
    }

    public function getDataTambahanBiayaPasienProperty()
    {
        return TambahanBiaya::query()
            ->biayaTambahanUntukHonorDokter($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                'pasien.nm_pasien',
                'reg_periksa.no_rkm_medis',
                'tambahan_biaya.no_rawat',
                'tambahan_biaya.nama_biaya',
                'penjab.png_jawab',
                'dokter.nm_dokter',
                "coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-')",
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
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            TambahanBiaya::query()
                ->biayaTambahanUntukHonorDokter($this->tglAwal, $this->tglAkhir)
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
            'Laporan Tambahan Biaya Pasien untuk Honor Dokter',
            now()->translatedFormat('d F Y'),
            'Periode '. carbon($this->tglAwal)->format('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->format('d F Y'),
        ];
    }
}
