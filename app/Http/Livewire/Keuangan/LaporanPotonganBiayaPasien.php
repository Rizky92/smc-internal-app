<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\PenguranganBiaya;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanPotonganBiayaPasien extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

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

    public function render()
    {
        return view('livewire.keuangan.laporan-potongan-biaya-pasien')
            ->layout(BaseLayout::class, ['title' => 'Laporan Potongan Biaya Pasien']);
    }

    public function getDataPotonganBiayaPasienProperty()
    {
        return PenguranganBiaya::query()
            ->potonganBiayaPasien($this->periodeAwal, $this->periodeAkhir)
            ->search($this->cari, [
                "pasien.nm_pasien",
                "reg_periksa.no_rkm_medis",
                "pengurangan_biaya.no_rawat",
                "pengurangan_biaya.nama_pengurangan",
                "penjab.png_jawab",
                "dokter.nm_dokter",
                "coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-')",
                "poliklinik.nm_poli",
                "reg_periksa.status_lanjut",
                "reg_periksa.status_bayar",
            ])
            ->sortWithColumns($this->sortColumns, [
                'dokter_ralan' => DB::raw("dokter.nm_dokter"),
                'dokter_ranap' => DB::raw("coalesce(nullif(trim(dokter_pj.nm_dokter), ''), '-')"),
            ])
            ->paginate($this->perpage);
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = '';
        $this->sortColumns = [];
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            PenguranganBiaya::potonganBiayaPasien($this->periodeAwal, $this->periodeAkhir)->get()
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
            'Nama Potongan',
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
            'Laporan Pengurangan Biaya Pasien',
            carbon($this->periodeAwal)->format('d F Y') . ' s.d. ' . carbon($this->periodeAkhir)->format('d F Y'),
        ];
    }
}
