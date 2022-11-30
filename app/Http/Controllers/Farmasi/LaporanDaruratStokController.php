<?php

namespace App\Http\Controllers\Farmasi;

use App\Exports\DaruratStokExport;
use App\Http\Controllers\Controller;
use App\Models\Farmasi\DataBarang;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Storage;

class LaporanDaruratStokController extends Controller
{
    /**
     * Tampilkan halaman laporan darurat stok.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $daruratStok = DataBarang::daruratStok()->get();

        return view('admin.farmasi.darurat-stok.index', [
            'daruratStok' => $daruratStok,
        ]);
    }

    public function index2(Request $request)
    {
        $daruratStok = DataBarang::daruratStok()->get();

        return view('admin.farmasi.darurat-stok.index2', [
            'daruratStok' => $daruratStok,
        ]);
    }

    public function export()
    {
        $timestamp = now()->format('U');
        $export = Excel::store(
            new DaruratStokExport, 
            "excel/{$timestamp}_darurat_stok.xlsx",
            'public'
        );

        if (!$export) {
            return response('', 204);
        }

        return Storage::disk('public')->download("excel/{$timestamp}_darurat_stok.xlsx");
    }
}