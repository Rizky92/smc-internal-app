<?php

namespace App\Livewire\Pages\Antrian;

use App\Models\Perawatan\RegistrasiPasien;
use App\Models\Antrian\AntriPoli;
use App\Models\Perawatan\Poliklinik;
use App\Models\Kepegawaian\Dokter;
use Illuminate\Http\Request;
use Livewire\Component;

class AntrianPoli extends Component
{
    public $antrianPasien;
    public $namaDokter;
    public $namaPoli;
    public $nextAntrian;
    public $kd_poli;
    public $kd_dokter;

    public function mount($kd_poli, $kd_dokter)
    {
        $this->kd_poli = $kd_poli;
        $this->kd_dokter = $kd_dokter;

        $this->namaDokter = Dokter::where('kd_dokter', $kd_dokter)->value('nm_dokter');
        $this->namaPoli = Poliklinik::where('kd_poli', $kd_poli)->value('nm_poli');

        $this->antrianPasien = RegistrasiPasien::with(['poliklinik', 'dokterPoli'])
            ->select('no_reg', 'no_rawat', 'nm_pasien')
            ->join('pasien', 'reg_periksa.no_rkm_medis', '=', 'pasien.no_rkm_medis')
            ->where('kd_poli', $kd_poli)
            ->where('kd_dokter', $kd_dokter)
            ->where('tgl_registrasi', now()->format('Y-m-d'))
            ->where('stts', 'Belum')
            ->orderBy('no_reg')
            ->get();

        $this->nextAntrian = AntriPoli::select('antripoli.*', 'reg_periksa.no_reg')
            ->join('reg_periksa', 'antripoli.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('antripoli.kd_poli', $kd_poli)
            ->where('antripoli.kd_dokter', $kd_dokter)
            ->first();
    }

    public function checkDataChanges(Request $request, $kd_poli, $kd_dokter)
    {
        $tanggal = now()->format('Y-m-d');

        $nextAntrian = AntriPoli::select('antripoli.*', 'reg_periksa.no_reg')
            ->join('reg_periksa', 'antripoli.no_rawat', '=', 'reg_periksa.no_rawat')
            ->where('antripoli.kd_poli', $kd_poli)
            ->where('antripoli.kd_dokter', $kd_dokter)
            ->first();

        if ($nextAntrian) {
            $lastNoReg = $request->input('lastNoReg');
            if ($this->isDataChanged($nextAntrian, $lastNoReg)) {
                \Illuminate\Support\Facades\Log::info('Data Changed: ' . json_encode($nextAntrian));
                $response = ['changed' => true, 'data' => $nextAntrian];
            } else {
                \Illuminate\Support\Facades\Log::info('No Data Change');
                $response = ['changed' => false, 'data' => $nextAntrian];
            }
        } else {
            $response = ['changed' => true, 'data' => $nextAntrian + ['namaDokter' => $namaDokter, 'namaPoli' => $namaPoli]];
        }

        return response()->json($response);
    }

    private function isDataChanged($nextAntrian, $lastNoReg)
    {
        if ($lastNoReg !== $nextAntrian->no_reg) {
            return true;
        }
        return false;
    }


    public function render()
    {
        return view('livewire.pages.antrian.antrian-poli');
    }
}