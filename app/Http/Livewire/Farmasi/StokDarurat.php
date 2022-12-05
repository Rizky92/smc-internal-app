<?php

namespace App\Http\Livewire\Farmasi;

use App\Models\Farmasi\DataBarang;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Excel;

class StokDarurat extends Component
{
    use WithPagination;

    public $cari;

    public $perpage;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'clearFilters',
        'clearFiltersAndHardRefresh',
    ];

    protected function queryString()
    {
        return [
            'cari' => [
                'except' => '',
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
        $this->perpage = 25;
    }

    public function getBarangDaruratStokProperty()
    {
        return DataBarang::daruratStok()
            ->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.farmasi.stok-darurat')
            ->extends('layouts.admin', ['title' => 'Darurat Stok'])
            ->section('content');
    }


    public function exportToExcel()
    {
        session()->flash('excel.exporting', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/farmasi_{$timestamp}_daruratstok.xlsx";

        $config = [
            'path' => storage_path('app/public'),
        ];

        $row1 = 'RS Samarinda Medika Citra';
        $row2 = 'Laporan Darurat Stok Farmasi';
        $row3 = now()->format('d F Y');

        $columnHeaders = [
            'Kode',
            'Nama',
            'Satuan kecil',
            'Kategori',
            'Stok minimal',
            'Stok saat ini',
            'Saran order',
            'Supplier',
            'Harga Per Unit (Rp)',
            'Total Harga (Rp)',
        ];

        $data = DataBarang::daruratStok()
            ->cursor()
            ->toArray();

        (new Excel($config))
            ->fileName($filename)

            // page header
            ->mergeCells('A1:H1', $row1)
            ->mergeCells('A2:H2', $row2)
            ->mergeCells('A3:H3', $row3)

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
            ->insertText(4, 0, '')

            // insert data
            ->data($data)
            ->output();

        return Storage::disk('public')->download($filename);
    }

    public function clearFilters()
    {
        $this->cari = '';
        $this->page = 1;
        $this->perpage = 25;
    }

    public function clearFiltersAndHardRefresh()
    {
        $this->emit('cleanFilters');

        $this->forgetComputed();

        $this->emit('$refresh');
    }
}
