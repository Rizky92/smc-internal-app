<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Farmasi\PenjualanWalkIn;
use App\Models\Farmasi\ReturJual;
use App\Models\Perawatan\Registrasi;
use Illuminate\Http\Request;

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
        $kunjunganRalan = Registrasi::totalKunjunganRalan();
        $kunjunganRanap = Registrasi::totalKunjunganRanap();
        $kunjunganIgd = Registrasi::totalKunjunganIGD();
        $kunjunganWalkIn = PenjualanWalkIn::totalKunjunganWalkIn();
        $kunjunganTotal = [];

        $totalReturObat = ReturJual::totalReturObat();

        dd($totalReturObat);

        foreach ($kunjunganRalan as $key => $data) {
            $kunjunganTotal[$key] = $kunjunganRalan[$key] + $kunjunganRanap[$key] + $kunjunganIgd[$key] + $kunjunganWalkIn[$key];
        }        

        return view('admin.farmasi.laporan-tahunan.index', [
            'kunjunganRalan' => $kunjunganRalan,
            'kunjunganRanap' => $kunjunganRanap,
            'kunjunganIgd' => $kunjunganIgd,
            'kunjunganWalkIn' => $kunjunganWalkIn,
            'kunjunganTotal' => $kunjunganTotal,
            'totalReturObat' => $totalReturObat,
        ]);
    }
}
