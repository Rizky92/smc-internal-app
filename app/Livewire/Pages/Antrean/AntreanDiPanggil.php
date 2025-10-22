<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Aplikasi\Pintu;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

class AntreanDiPanggil extends Component
{
    public string $kd_pintu;

    public bool $isCalling = false;

    public $antreanDipanggilSekarang = null;

    protected $listeners = ['updateStatus'];

    public function getAntreanDiPanggilProperty()
    {
        return Pintu::query()
            ->antreanPerPintu($this->kd_pintu)
            ->where('antripintu_smc.status', '1')
            ->latest('antripintu_smc.waktu_panggil')
            ->first();
    }

    public function getAntreanSedangPeriksaProperty()
    {
        return Pintu::query()
            ->antreanPerPintu($this->kd_pintu)
            ->where('antripintu_smc.status', '0')
            ->latest('antripintu_smc.waktu_panggil')
            ->first();
    }

    public function call(): void
    {
        if ($this->isCalling) {
            return;
        }

        $antrean = $this->getAntreanDiPanggilProperty();

        if ($antrean) {
            $this->isCalling = true;
            $this->antreanDipanggilSekarang = [
                'no_rawat'  => $antrean->no_rawat,
                'kd_poli'   => $antrean->kd_poli,
                'kd_dokter' => $antrean->kd_dokter,
                'no_reg'    => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'kd_pintu'  => $antrean->kd_pintu,
                'nm_pintu'  => $antrean->nm_pintu,
            ];
            $this->dispatchBrowserEvent('play-voice', [
                'no_reg'    => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'nm_pintu'  => $antrean->nm_pintu,
            ]);
        }
    }

    public function updateStatus()
    {
        $antrean = $this->antreanDipanggilSekarang;

        if ($antrean) {
            try {
                DB::connection('mysql_sik')->transaction(function () use (&$antrean) {
                    DB::connection('mysql_sik')
                        ->table('antripintu_smc')
                        ->where('no_rawat', $antrean['no_rawat'])
                        ->where('kd_pintu', $antrean['kd_pintu'])
                        ->where('status', '1')
                        ->update(['status' => '0']);
                });
            } catch (Exception $e) {
                Log::error($e);
            }
        }

        $this->isCalling = false;
        $this->antreanDipanggilSekarang = null;
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.antrean-di-panggil');
    }
}
