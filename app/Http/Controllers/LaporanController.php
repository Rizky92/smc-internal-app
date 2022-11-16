<?php

namespace App\Http\Controllers;

use App\DataBarang;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __invoke(Request $request)
    {
        $janganTampilkanStokMinimalNol = $request->boolean('stok_minimal_nol');

        $daruratStok = DataBarang::daruratStok();

        if ($janganTampilkanStokMinimalNol) {
            $daruratStok = $daruratStok->janganTampilkanStokMinimalNol();

            $request->session()->flash('tidak_ada_stok_minimal_nol', 'Menampilkan stok minimal lebih dari nol.');
        }
        
        return view('admin.laporan.index', [
            'daruratStok' => $daruratStok->get(),
        ]);
    }
}