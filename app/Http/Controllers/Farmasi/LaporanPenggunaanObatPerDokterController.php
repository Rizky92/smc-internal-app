<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Resep;
use Illuminate\Http\Request;

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
        $penggunaanObatPerDokter = Resep::penggunaanObatPerDokter(now()->format('Y-m-d'), now()->format('Y-m-d'))->get();
        
        return view('admin.farmasi.penggunaan-obat-perdokter.index', [
            'obatPerDokter' => $penggunaanObatPerDokter,
        ]);
    }
}
