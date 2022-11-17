<?php

namespace App\Http\Controllers;

use App\DataBarang;
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
    public function index(Request $request)
    {
        // DB::enableQueryLog();

        $daruratStok = DataBarang::daruratStok()->get();

        // dd(DB::getQueryLog());

        return view('admin.darurat-stok.index', [
            'daruratStok' => $daruratStok,
        ]);
    }
}