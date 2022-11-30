<?php

namespace App\Http\Livewire;

use App\Exports\LaporanStokMinmaxBarangLogistik;
use App\Models\Nonmedis\BarangNonmedis;
use App\Models\Nonmedis\MinmaxBarangNonmedis;
use App\Models\Nonmedis\SupplierNonmedis;
use Livewire\Component;
use Livewire\WithPagination;
use Storage;

class MinmaxStokBarang extends Component
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

    public $tampilkanSaranOrderNol = true;

    protected $paginationTheme = 'bootstrap';

    protected $rules = [
        'stokMin' => ['required', 'numeric', 'min:0'],
        'stokMax' => ['required', 'numeric', 'min:0'],
    ];

    protected $listeners = [
        'refreshData' => '$refresh',
    ];

    public function getItem($kodeBarang)
    {
        $barang = BarangNonmedis::daruratStok()->where('ipsrsbarang.kode_brng', $kodeBarang)->first();

        $this->kodeBarang = $kodeBarang;
        $this->namaBarang = $barang->nama_brng;
        $this->kodeSupplier = $barang->kode_supplier;
        $this->stokMin = $barang->stokmin;
        $this->stokMax = $barang->stokmax;
        $this->stokSekarang = $barang->stok;
        $this->saranOrder = $barang->saran_order;
    }

    public function exportToExcel()
    {
        $timestamp = now()->format('Ymd_His');

        $file = "excel/{$timestamp}_daruratstok_logistik.xlsx";

        $export = (new LaporanStokMinmaxBarangLogistik($timestamp, $this->cari ?? null, $this->tampilkanSaranOrderNol))
            ->store("excel/{$timestamp}_daruratstok_logistik.xlsx", 'public');

        if ($export) {
            return Storage::disk('public')->download($file);
        }
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
        $this->tampilkanSaranOrderNol = true;
    }

    public function render()
    {
        $barang = BarangNonmedis::daruratStok($this->cari, $this->tampilkanSaranOrderNol)
            ->paginate();

        $supplier = SupplierNonmedis::pluck('nama_suplier', 'kode_suplier');

        return view('livewire.minmax-stok-barang', [
            'barangLogistik' => $barang,
            'supplier' => $supplier,
        ]);
    }
}
