<?php

namespace App\Http\Livewire;

use App\Http\Controllers\Logistik\InputStokMinMaxController;
use App\Models\Nonmedis\BarangNonmedis;
use App\Models\Nonmedis\MinmaxBarangNonmedis;
use App\Models\Nonmedis\SupplierNonmedis;
use Livewire\Component;

class MinmaxStokBarang extends Component
{
    public $kodeBarang;
    public $namaBarang;
    public $kodeSupplier;
    public $stokMin;
    public $stokMax;
    public $stokSekarang;
    public $saranOrder;

    protected $rules = [
        'stokMin' => ['required', 'numeric', 'min:0'],
        'stokMax' => ['required', 'numeric', 'min:0'],
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
        
    }

    public function simpan()
    {
        $barang = MinmaxBarangNonmedis::find($this->kodeBarang);

        $barang->stok_min = $this->stokMin;
        $barang->stok_max = $this->stokMax;
        $barang->kode_suplier = $this->kodeSupplier;

        $barang->save();

        session()->flash('saved.title', 'Sukses!');
        session()->flash('saved.content', 'Data berhasil disimpan!');

        return 0;
    }

    public function render()
    {
        $barang = BarangNonmedis::daruratStok()->get();

        $supplier = SupplierNonmedis::pluck('nama_suplier', 'kode_suplier');

        return view('livewire.minmax-stok-barang', [
            'barang' => $barang,
            'supplier' => $supplier,
        ]);
    }
}
