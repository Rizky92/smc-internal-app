<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Logistik\BarangNonMedis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Excel;

class StokDaruratLogistik extends Component
{
    use WithPagination;
    
    public $cari;

    public $tampilkanSaranOrderNol;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
            ],
            'tampilkanSaranOrderNol' => [
                'except' => true,
            ],
            'perpage' => [
                'except' => 25,
            ],
            'page' => [
                'except' => 1,
            ],
        ];
    }

    protected $listeners = [
        'beginExcelExport',
        'clearFilters',
    ];

    public function mount()
    {
        $this->cari = '';
        $this->tampilkanSaranOrderNol = true;
        $this->perpage = 25;
    }

    public function getStokDaruratLogistikProperty()
    {
        return BarangNonMedis::daruratStok(Str::lower($this->cari), $this->tampilkanSaranOrderNol)
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.logistik.stok-darurat-logistik')
            ->extends('layouts.admin', ['title' => 'Stok Darurat Barang Logistik'])
            ->section('content');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_darurat_stok_logistik.xlsx";

        $config = [
            'path' => storage_path('app/public'),
        ];

        $row1 = 'RS Samarinda Medika Citra';
        $row2 = 'Minmax Stok Barang Non Medis';
        $row3 = now()->format('d F Y');

        $columnHeaders = [
            'Kode',
            'Nama',
            'Satuan',
            'Jenis',
            'Supplier',
            'Min',
            'Max',
            'Saat ini',
            'Saran order',
            'Harga Per Unit (Rp)',
            'Total Harga (Rp)',
        ];

        $data = BarangNonMedis::daruratStok($this->cari, $this->tampilkanSaranOrderNol)
            ->cursor()
            ->toArray();

        (new Excel($config))
            ->fileName($filename)

            // page header
            ->mergeCells('A1:K1', $row1)
            ->mergeCells('A2:K2', $row2)
            ->mergeCells('A3:K3', $row3)

            // column header
            ->insertText(3, 0, $columnHeaders[0])
            ->insertText(3, 1, $columnHeaders[1])
            ->insertText(3, 2, $columnHeaders[2])
            ->insertText(3, 3, $columnHeaders[3])
            ->insertText(3, 4, $columnHeaders[4])
            ->insertText(3, 5, $columnHeaders[5])
            ->insertText(3, 6, $columnHeaders[6])
            ->insertText(3, 7, $columnHeaders[7])
            ->insertText(3, 8, $columnHeaders[8])
            ->insertText(3, 9, $columnHeaders[9])
            ->insertText(3, 10, $columnHeaders[10])

            // empty row untuk insert data
            ->insertText(4, 0, '')

            ->data($data)
            ->output();

        return Storage::disk('public')->download($filename);
    }

    public function exportToExcel()
    {
        session()->flash('excel.exporting', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }
}
