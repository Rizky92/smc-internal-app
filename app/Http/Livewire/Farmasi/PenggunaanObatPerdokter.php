<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\ResepObat;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class PenggunaanObatPerdokter extends Component
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

    protected function queryString(): array
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
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->perpage = 25;
    }

    public function getObatPerDokterProperty()
    {
        return ResepObat::query()
            ->penggunaanObatPerDokter($this->periodeAwal, $this->periodeAkhir, Str::lower($this->cari))
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.penggunaan-obat-perdokter')
            ->layout(BaseLayout::class, ['title' => 'Penggunaan Obat Per Dokter Peresep']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_obat_perdokter.xlsx";

        $headerTglAwal = Carbon::parse($this->periodeAwal)->format('d F Y');
        $headerTglAkhir = Carbon::parse($this->periodeAkhir)->format('d F Y');

        $titles = [
            'RS Samarinda Medika Citra',
            'Laporan Penggunaan Obat Per Dokter Peresep',
            "{$headerTglAwal} - {$headerTglAkhir}",
        ];

        $columnHeaders = ['No. Resep', 'Tgl. Validasi', 'Jam', 'Nama Obat', 'Jumlah', 'Dokter Peresep', 'Asal', 'Asal Poli'];

        $data = ResepObat::penggunaanObatPerDokter($this->periodeAwal, $this->periodeAkhir)
            ->cursor()
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
        $this->resetPage();
        $this->perpage = 25;

        $this->emit('$refresh');
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
