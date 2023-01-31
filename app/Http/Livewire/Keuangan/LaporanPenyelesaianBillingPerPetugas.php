<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\NotaSelesai;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanPenyelesaianBillingPerPetugas extends Component
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

    public function getBillingYangDiselesaikanProperty()
    {
        return NotaSelesai::query()
            ->billingYangDiselesaikan($this->periodeAwal, $this->periodeAkhir)
            ->search($this->cari, [
                "nota_selesai.no_rawat",
                "pasien.no_rkm_medis",
                "trim(pasien.nm_pasien)",
                "nota_pasien.no_nota",
                "ifnull(concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal), '-')",
                "nota_selesai.status_pasien",
                "nota_selesai.bentuk_bayar",
                "nota_pasien.besar_bayar",
                "penjab.png_jawab",
                "nota_selesai.tgl_penyelesaian",
                "concat(nota_selesai.user_id, ' ', pegawai.nama)",
            ])
            ->sortWithColumns($this->sortColumns, [
                'nm_pasien' => DB::raw("trim(pasien.nm_pasien)"),
                'ruangan' => DB::raw("ifnull(concat(kamar.kd_kamar, ' ', bangsal.nm_bangsal), '-')"),
                'nama_pegawai' => DB::raw("concat(nota_selesai.user_id, ' ', pegawai.nama)"),
            ])
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.keuangan.laporan-penyelesaian-billing-per-petugas')
            ->layout(BaseLayout::class, ['title' => 'Laporan Penyelesaian Billing Pasien per Petugas']);
    }

    public function tarikDataTerbaru()
    {
        NotaSelesai::refreshModel();

        $this->fullRefresh();

        $this->flashSuccess("Data Berhasil Diperbaharui!");
    }

    protected function defaultValues()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->sortColumns = [];
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            NotaSelesai::query()
                ->billingYangDiselesaikan($this->periodeAwal, $this->periodeAkhir)
                ->get()
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            '#',
            'No. Rawat',
            'No. RM',
            'Pasien',
            'No. Nota',
            'Ruang Inap',
            'Jenis Perawatan',
            'Bentuk Pembayaran',
            'Nominal Yang Dibayarkan (RP)',
            'Asuransi',
            'Diselesaikan Pada',
            'Oleh Petugas',
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            'RS Samarinda Medika Citra',
            'Laporan Penyelesaian Billing Pasien Per Petugas',
            Carbon::parse($this->periodeAwal)->format('d F Y') . ' s.d. ' . Carbon::parse($this->periodeAkhir)->format('d F Y'),
        ];
    }
}
