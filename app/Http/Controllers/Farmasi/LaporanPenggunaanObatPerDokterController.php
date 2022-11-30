<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Farmasi\Resep;
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
        $penggunaanObatPerDokter = Resep::penggunaanObatPerDokter(
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        )->paginate();
        
        return view('admin.farmasi.penggunaan-obat-perdokter.index', [
            'obatPerDokter' => $penggunaanObatPerDokter,
        ]);
    }
}
