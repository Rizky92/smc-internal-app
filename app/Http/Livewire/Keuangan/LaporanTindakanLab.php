<?php

namespace App\Http\Livewire\Keuangan;

use App\Models\Laboratorium\HasilPeriksaLab;
use App\Support\Traits\Livewire\DeferredLoading;
use App\Support\Traits\Livewire\ExcelExportable;
use App\Support\Traits\Livewire\Filterable;
use App\Support\Traits\Livewire\FlashComponent;
use App\Support\Traits\Livewire\LiveTable;
use App\Support\Traits\Livewire\MenuTracker;
use App\View\Components\BaseLayout;
use Carbon\CarbonImmutable;
use Livewire\Component;
use Livewire\WithPagination;

class LaporanTindakanLab extends Component
{
    use WithPagination, FlashComponent, Filterable, ExcelExportable, LiveTable, MenuTracker, DeferredLoading;

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

    public function getDataLaporanTindakanLabProperty()
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

    public function render()
    {
        return view('livewire.keuangan.laporan-tindakan-lab')
            ->layout(BaseLayout::class, ['title' => 'Laporan Jumlah Tindakan Laboratorium']);
    }

    protected function defaultValues()
    {
        $this->tglAwal = now()->startOfMonth()->format('Y-m-d');
        $this->tglAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            HasilPeriksaLab::laporanTindakanLab($this->tglAwal, $this->tglAkhir)->get(),
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
        return [
            'RS Samarinda Medika Citra',
            'Laporan Jumlah Tindakan Laboratorium',
            now()->format('d F Y'),
            CarbonImmutable::parse($this->tglAwal)->format('d F Y') . ' - ' . CarbonImmutable::parse($this->tglAkhir)->format('d F Y'),
        ];
    }
}
