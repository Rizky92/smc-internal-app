<?php

namespace App\Http\Livewire;

use App\Exports\LaporanStokMinmaxBarangLogistik;
use App\Exports\LogistikStokMinmaxBarang;
use App\Models\Nonmedis\BarangNonmedis;
use App\Models\Nonmedis\MinmaxBarangNonmedis;
use App\Models\Nonmedis\SupplierNonmedis;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class MinmaxStokBarangLogistik extends Component
{
    use WithPagination;

    public $kodeBarang;
    
    public $namaBarang;
    
    public $kodeSupplier;
    
    public $stokMin;
    
    public $stokMax;
    
    public $stokSekarang;
    
    public $saranOrder;

    public $cari;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'stokMin' => ['required', 'numeric', 'min:0'],
        'stokMax' => ['required', 'numeric', 'min:0'],
    ];

    protected $listeners = [
        'refreshData' => '$refresh',
        'processExcelExport',
    ];

    public function getItem($kodeBarang)
    {
        $barang = BarangNonmedis::denganMinmax()->where('ipsrsbarang.kode_brng', $kodeBarang)->first();

        $this->kodeBarang = $kodeBarang;
        $this->namaBarang = $barang->nama_brng;
        $this->kodeSupplier = $barang->kode_supplier;
        $this->stokMin = $barang->stokmin;
        $this->stokMax = $barang->stokmax;
        $this->stokSekarang = $barang->stok;
        $this->saranOrder = $barang->saran_order;
    }

    public function processExcelExport()
    {
        $timestamp = now()->format('Ymd_His');

        $filename = "excel/{$timestamp}_minmax_stok_logistik.xlsx";

        (new LogistikStokMinmaxBarang($this->cari))
            ->store($filename, 'public');
        
        return Storage::disk('public')->download($filename);
    }

    public function exportToExcel()
    {
        session()->flash('excel.exporting', 'Proses ekspor laporan dimulai! Silahkan tunggu beberapa saat. Mohon untuk tidak menutup halaman agar proses ekspor dapat berlanjut.');

        $this->emit('processExcelExport');
    }

    public function simpan($kodeSuplier)
    {
        $minmaxBarang = MinmaxBarangNonmedis::find($this->kodeBarang);

        $minmaxBarang->stok_min = $this->stokMin;
        $minmaxBarang->stok_max = $this->stokMax;
        $minmaxBarang->kode_suplier = $kodeSuplier;

        $minmaxBarang->save();

        $this->clear();

        session()->flash('saved.title', 'Sukses!');
        session()->flash('saved.content', 'Data berhasil disimpan!');

        return 0;
    }

    private function clear()
    {
        $this->kodeBarang = null;
        $this->namaBarang = null;
        $this->kodeSupplier = null;
        $this->stokMin = null;
        $this->stokMax = null;
        $this->stokSekarang = null;
        $this->saranOrder = null;
        $this->cari = null;
    }

    public function render()
    {
        $barang = BarangNonmedis::denganMinmax($this->cari)
            ->paginate();

        $supplier = SupplierNonmedis::pluck('nama_suplier', 'kode_suplier');

        return view('livewire.minmax-stok-barang-logistik', [
            'barangLogistik' => $barang,
            'supplier' => $supplier,
        ]);
    }
}
