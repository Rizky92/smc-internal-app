<?php

namespace App\Exports;

use App\Registrasi;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;

class RekamMedisExport implements FromView
{
    use Exportable;

    private $periodeAwal;
    private $periodeAkhir;

    public function __construct($periodeAwal = null, $periodeAkhir = null)
    {
        $this->periodeAwal = $periodeAwal ?? now()->startOfMonth();
        $this->periodeAkhir = $periodeAkhir ?? now()->endOfMonth();
    }

    public function view(): View
    {
        return view('admin.rekam-medis.laporan-statistik.table', [
            'statistik' => Registrasi::laporanStatistik($this->periodeAwal, $this->periodeAkhir)->get(),
        ]);
    }
}
