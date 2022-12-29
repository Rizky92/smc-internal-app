<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\Inventaris\SuratPemesananObat;
use App\Support\Traits\Livewire\FlashComponent;
use App\View\Components\BaseLayout;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Rizky92\Xlswriter\ExcelExport;

class RingkasanPerbandinganBarangPO extends Component
{
    use WithPagination, FlashComponent;

    public $cari;

    public $periodeAwal;

    public $periodeAkhir;

    public $perpage;

    public $hanyaTampilkanBarangSelisih;

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
            'perpage' => [
                'except' => 25,
            ],
            'periodeAwal' => [
                'except' => now()->startOfMonth()->format('Y-m-d'),
                'as' => 'periode_awal',
            ],
            'periodeAkhir' => [
                'except' => now()->endOfMonth()->format('Y-m-d'),
                'as' => 'periode_akhir',
            ],
            'hanyaTampilkanBarangSelisih' => [
                'except' => false,
                'as' => 'barang_selisih',
            ],
        ];
    }

    public function mount()
    {
        $this->cari = '';
        $this->perpage = 25;
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');
        $this->hanyaTampilkanBarangSelisih = false;
    }

    public function getPerbandinganOrderObatPOProperty()
    {
        return SuratPemesananObat::perbandinganPemesananObatPO(
            $this->periodeAwal,
            $this->periodeAkhir,
            Str::lower($this->cari),
            $this->hanyaTampilkanBarangSelisih
        )
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.ringkasan-perbandingan-barang-p-o')
            ->layout(BaseLayout::class, ['title' => 'Ringkasan Perbandingan Barang PO Farmasi']);
    }

    public function exportToExcel()
    {
        $this->flashInfo('Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "{$timestamp}_farmasi_perbandingan_po_obat.xlsx";

        $titles = [
            'RS Samarinda Medika Citra',
            'Ringkasan Perbandingan PO Obat',
            now()->format('d F Y'),
        ];

        $columnHeaders = [
            'No. Pemesanan',
            'Nama',
            'Supplier Tujuan',
            'Supplier yang Mendatangkan',
            'Jumlah Dipesan',
            'Jumlah yang Datang',
            'Selisih',
        ];

        $data = SuratPemesananObat::perbandinganPemesananObatPO(
            $this->periodeAwal,
            $this->periodeAkhir,
            '',
            $this->hanyaTampilkanBarangSelisih
        )
            ->get()
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
        $this->perpage = 25;
        $this->periodeAwal = now()->startOfMonth()->format('Y-m-d');
        $this->periodeAkhir = now()->endOfMonth()->format('Y-m-d');

        $this->searchData();
    }

    public function fullRefresh()
    {
        $this->forgetComputed();

        $this->resetFilters();
    }
}
