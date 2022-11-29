<?php

namespace App\Http\Livewire;

use App\Models\Nonmedis\BarangNonmedis;
use App\Models\Nonmedis\SupplierNonmedis;
use Livewire\Component;

class MinmaxStokBarang extends Component
{
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
