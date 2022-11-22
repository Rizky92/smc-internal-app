<?php

namespace App\Http\Controllers\Farmasi;

use App\DataBarang;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanDaruratStokController extends Controller
{
    /**
     * Tampilkan halaman laporan darurat stok.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $daruratStok = DataBarang::daruratStok()->get();

        return view('admin.farmasi.darurat-stok.index', [
            'daruratStok' => $daruratStok,
        ]);
    }
}