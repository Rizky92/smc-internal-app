<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\RekamMedis\DemografiPasien;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class LaporanDemografi extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $perpage;

    public $periodeAwal;

    public $periodeAkhir;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'searchData',
        'resetFilters',
        'fullRefresh',
    ];

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'periodeAwal' => [
                'except' => now()->startOfMonth()->format('Y-m-d'),
                'as' => 'periode_awal',
            ],
            'periodeAkhir' => [
                'except' => now()->endOfMonth()->format('Y-m-d'),
                'as' => 'periode_akhir',
            ],
            'perpage' => [
                'except' => 25,
            ],
        ];
    }

    public function mount()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;
    }

    public function getDemografiPasienProperty()
    {
        return DemografiPasien::query()
            ->whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.rekam-medis.laporan-demografi')
            ->layout(BaseLayout::class, ['title' => 'Demografi Pasien']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_laporan_demografi_pasien.xlsx";

        $titles = [
            'RS Samarinda Medika Citra',
            'Laporan Demografi Pasien',
            now()->format('d F Y'),
        ];

        $columnHeaders = [
            'Kecamatan',
            'No. RM',
            'No. Registrasi',
            'Pasien',
            'Alamat',
            '0 - < 28 Hr',
            '28 Hr - 1 Th',
            '1 - 4 Th',
            '5 - 14 Th',
            '15 - 24 Th',
            '25 - 44 Th',
            '45 - 64 Th',
            '> 64 Th',
            'PR',
            'LK',
            'Diagnosa',
            'Agama',
            'Pendidikan',
            'Bahasa',
            'Suku',
        ];

        $excel = ExcelExport::make($filename)
            ->setPageHeaders($titles)
            ->setColumnHeaders($columnHeaders)
            ->setData(DemografiPasien::laporanDemografiExcel($this->periodeAwal, $this->periodeAkhir)->get());

        return $excel->export();
    }

    public function searchData()
    {
        $this->resetPage();

        $this->emit('$refresh');
    }

    public function resetFilters()
    {
        $this->cari = '';
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
