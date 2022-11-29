<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Registrasi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LaporanTahunanController extends Controller
{
    /**
     * Tampilkan laporan farmasi tahunan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $kunjunganRalan = Registrasi::laporanKunjunganRalan()
            ->get()
            ->map(function ($registrasi) {
                /** @var \App\Registrasi $registrasi */
                
                return [$registrasi->tgl => $registrasi->jumlah];
            });

        dd($kunjunganRalan);

        return view('admin.farmasi.laporan-tahunan.index');
    }
}
