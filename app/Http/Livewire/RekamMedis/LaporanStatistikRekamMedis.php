<?php

namespace App\Http\Livewire\RekamMedis;

use App\Models\RekamMedis\StatistikRekamMedis;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class LaporanStatistikRekamMedis extends Component
{
    use WithPagination, FlashComponent;

    public $periodeAwal;

    public $periodeAkhir;

    public $cari;

    public $perpage;

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
            'page' => [
                'except' => 1,
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
        $this->page = 1;
        $this->perpage = 25;
    }

    public function getDataLaporanStatistikProperty()
    {
        return StatistikRekamMedis::query()
            ->denganPencarian($this->cari)
            ->whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
            ->orderBy('no_rawat')
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.rekam-medis.laporan-statistik-rekam-medis')
            ->layout(BaseLayout::class, ['title' => 'Laporan Statistik']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_rekammedis_laporan_statistik.xlsx";

        $dateStart = Carbon::parse($this->periodeAwal)->format('d F Y');
        $dateEnd = Carbon::parse($this->periodeAkhir)->format('d F Y');

        $titles = [
            'RS Samarinda Medika Citra',
            'Laporan Statistik Rekam Medis',
            "{$dateStart} - {$dateEnd}",
        ];

        $columnHeaders = [
            'No. Rawat',
            'No. RM',
            'Nama Pasien',
            'NIK',
            'L / P',
            'Tgl. Lahir',
            'Umur',
            'Agama',
            'Suku',
            'Jenis Perawatan',
            'Pasien Lama / Baru',
            'Status Ralan',
            'Tgl. Masuk',
            'Jam Masuk',
            'Tgl. Pulang',
            'Jam Pulang',
            'Diagnosa Masuk',
            'ICD Diagnosa',
            'Diagnosa',
            'ICD Tindakan',
            'Tindakan',
            'DPJP',
            'Poli',
            'Kelas',
            'Penjamin',
            'Status Bayar',
            'Status Pulang',
            'No. HP',
            'Alamat',
            'Kunjungan ke',
        ];

        $data = StatistikRekamMedis::whereBetween('tgl_registrasi', [$this->periodeAwal, $this->periodeAkhir])
            ->orderBy('no_rawat')
            ->lazy()
            ->toArray();

        $excel = ExcelExport::make($filename)
            ->setPageHeaders($titles)
            ->setColumnHeaders($columnHeaders)
            ->setData($data);

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
