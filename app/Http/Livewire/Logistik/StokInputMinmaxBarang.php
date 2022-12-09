<?php

namespace App\Http\Livewire\Logistik;

use App\Models\Nonmedis\BarangNonmedis;
use App\Models\Nonmedis\MinmaxBarangNonmedis;
use App\Models\Nonmedis\SupplierNonmedis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Vtiful\Kernel\Excel;

class StokInputMinmaxBarang extends Component
{
    use WithPagination;

    public $cari;

    public $perpage;

    public $kodeSupplier;
    
    public $stokMin;
    
    public $stokMax;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'beginExcelExport',
        'clearFilters',
        'clearFiltersAndHardRefresh',
    ];

    protected $rules = [
        'stokMin' => ['required', 'numeric', 'min:0'],
        'stokMax' => ['required', 'numeric', 'min:0'],
        'kodeSupplier' => ['required', 'string'],
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
        $this->cari = '';
        $this->page = 1;
        $this->perpage = 25;
    }

    public function getSupplierProperty()
    {
        return SupplierNonmedis::pluck('nama_suplier', 'kode_suplier');
    }

    public function getBarangLogistikProperty()
    {
        return BarangNonmedis::denganMinmax(Str::lower($this->cari))->paginate($this->perpage);
    }

    public function render()
    {
        return view('livewire.logistik.stok-input-minmax-barang')
            ->extends('layouts.admin', ['title' => 'Stok Minmax Barang Logistik'])
            ->section('content');
    }

    public function exportToExcel()
    {
        session()->flash('excel.exporting', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('beginExcelExport');
    }

    public function simpan($kodeBarang, $stokMin, $stokMax, $kodeSupplier)
    {
        $kodeSupplier = $kodeSupplier != '-' ? $kodeSupplier : null;

        $minmaxBarang = MinmaxBarangNonmedis::findOrNew($kodeBarang);

        $minmaxBarang->stok_min = $stokMin;
        $minmaxBarang->stok_max = $stokMax;
        $minmaxBarang->kode_suplier = $kodeSupplier;

        $minmaxBarang->save();

        $this->clearFilters();

        session()->flash('saved.title', 'Sukses!');
        session()->flash('saved.content', 'Data berhasil disimpan!');
    }

    public function beginExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_stokminmax_barang.xlsx";

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

        $data = BarangNonmedis::denganMinmax($this->cari, true)
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
