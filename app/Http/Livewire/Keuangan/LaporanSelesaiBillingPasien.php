<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Keuangan\NotaSelesai;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LaporanSelesaiBillingPasien extends Component
{
    use FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker;

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

    public function getBillingYangDiselesaikanProperty()
    {
        return NotaSelesai::query()
            ->billingYangDiselesaikan($this->tglAwal, $this->tglAkhir)
            ->search($this->cari, [
                "nota_selesai.id",
                "nota_selesai.no_rawat",
                "pasien.no_rkm_medis",
                "pasien.nm_pasien",
                "ifnull(nota_pasien.no_nota, '-')",
                "ifnull(kamar.kd_kamar, '-')",
                "ifnull(bangsal.kd_bangsal, '-')",
                "ifnull(bangsal.nm_bangsal, '-')",
                "nota_selesai.status_pasien",
                "nota_selesai.bentuk_bayar",
                "penjab.png_jawab",
                "nota_selesai.tgl_penyelesaian",
                "nota_selesai.user_id",
                "pegawai.nama",
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
        return view('livewire.keuangan.laporan-selesai-billing-pasien')
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
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            NotaSelesai::query()
                ->billingYangDiselesaikan($this->tglAwal, $this->tglAkhir)
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
            'Laporan Penyelesaian Billing Pasien per Petugas',
            now()->translatedFormat('d F Y'),
            'Periode ' . carbon($this->tglAwal)->format('d F Y') . ' s.d. ' . carbon($this->tglAkhir)->format('d F Y'),
        ];
    }
}
