<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
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
        $ralan = Registrasi::kunjunganRalan()->get();
        $ranap = Registrasi::kunjunganRanap()->get();
        $igd = Registrasi::kunjunganIGD()->get();
        $total = Registrasi::kunjunganTotal()->get();

        $kunjungan = collect([
            $ralan,
            $ranap,
            $igd,
            $total,
        ]);

        dd($kunjungan->flatten(1)->mapToGroups(function ($item) {
            return [
                $item['kategori'] => [
                    $item['bulan'] => $item['jumlah'],
                ],
            ];
        })->toArray());

        return view('admin.farmasi.laporan-tahunan.index');
    }
}
