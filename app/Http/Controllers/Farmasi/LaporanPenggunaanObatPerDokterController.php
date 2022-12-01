<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LaporanPenggunaanObatPerDokterController extends Controller
{
    /**
     * Tampilkan halaman penggunaan obat per dokter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return view('admin.farmasi.penggunaan-obat-perdokter.index');
    }
}
