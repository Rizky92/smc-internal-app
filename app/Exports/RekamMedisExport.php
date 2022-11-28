<?php

namespace App\Exports;

use App\Registrasi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RekamMedisExport implements FromView
{
    private $periodeAwal;
    private $periodeAkhir;

    public function __construct($periodeAwal, $periodeAkhir)
    {
        $this->periodeAwal = $periodeAwal ?? now()->startOfMonth();
        $this->periodeAkhir = $periodeAkhir ?? now()->endOfMonth();
    }

    public function view(): View
    {
        return view('admin.rekam-medis.laporan-statistik.table', [
            'statistik' => Registrasi::laporanStatistik($this->periodeAwal, $this->periodeAkhir)
                ->orderBy('no_rawat')
                ->orderBy('no_reg')
                ->get()
        ]);
    }
}
