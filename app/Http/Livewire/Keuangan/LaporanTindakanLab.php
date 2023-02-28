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
use Livewire\Component;
use Livewire\WithPagination;

class LaporanTindakanLab extends Component
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

    public function getDataLaporanTindakanLabProperty()
    {
        return HasilPeriksaLab::query()
            ->laporanTindakanLab($this->periodeAwal, $this->periodeAkhir)
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
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
    }

    protected function dataPerSheet(): array
    {
        return [
            //
        ];
    }

    protected function columnHeaders(): array
    {
        return [
            //
        ];
    }

    protected function pageHeaders(): array
    {
        return [
            //
        ];
    }
}
