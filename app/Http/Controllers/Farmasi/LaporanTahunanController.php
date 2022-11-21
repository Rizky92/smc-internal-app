<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LaporanTahunanController extends Controller
{
    /**
     * Tampilkan laporan farmasi tahunan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('admin.farmasi.laporan-tahunan.index');
    }
}
