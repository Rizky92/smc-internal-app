<?php
namespace App\Http\Controllers;


use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Antrian\AntriPoli;
use App\Models\Perawatan\Poliklinik;
use App\Models\Kepegawaian\Dokter;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class AntrianPoliController extends Component
{
    public function show($kd_poli, $kd_dokter)
{
    $tanggal = now()->format('Y-m-d');

    $antrianPasien = RegistrasiPasien::with(['poliklinik', 'dokterPoli'])
        ->select('no_reg', 'no_rawat', 'nm_pasien')
        ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
        ->where('kd_poli', $kd_poli)
        ->where('kd_dokter', $kd_dokter)
        ->where('tgl_registrasi', $tanggal)
        ->where('stts', 'Belum')
        ->orderBy('no_reg')
        ->get();

    $namaDokter = Dokter::where('kd_dokter', $kd_dokter)->value('nm_dokter');
    $namaPoli = Poliklinik::where('kd_poli', $kd_poli)->value('nm_poli');

    $nextAntrian = AntriPoli::select('antripoli.*', 'reg_periksa.no_reg')
    ->join('reg_periksa', 'antripoli.no_rawat', '=', 'reg_periksa.no_rawat')
    ->where('antripoli.kd_poli', $kd_poli)
    ->where('antripoli.kd_dokter', $kd_dokter)
    ->first();

    return view('antrian-poli', compact('antrianPasien', 'namaDokter', 'namaPoli', 'nextAntrian'));
}

}
