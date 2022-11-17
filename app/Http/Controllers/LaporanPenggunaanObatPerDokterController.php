<?php

namespace App\Http\Controllers;

use App\Resep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanPenggunaanObatPerDokterController extends Controller
{
    /**
     * Tampilkan halaman penggunaan obat per dokter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        DB::enableQueryLog();
        
        dump(Resep::penggunaanObatPerDokter(now()->format('Y-m-d'), now()->format('Y-m-d'))->get());

        dd(DB::getQueryLog());

        return view('admin.penggunaan-obat-perdokter.index');
    }
}
