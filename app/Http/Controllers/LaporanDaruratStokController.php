<?php

namespace App\Http\Controllers;

use App\DataBarang;
use Illuminate\Http\Request;

class LaporanDaruratStokController extends Controller
{
    /**
     * Tampilkan halaman laporan darurat stok.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('admin.laporan.index', [
            'daruratStok' => DataBarang::daruratStok()->get(),
        ]);
    }
}