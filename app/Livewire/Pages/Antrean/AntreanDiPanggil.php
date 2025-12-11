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

    // Untuk Livewire v3
    protected $listeners = ['updateStatus'];

    public function getAntreanDiPanggilProperty()
    {
        return Pintu::query()
            ->antreanPerPintu($this->kd_pintu)
            ->where('antripintu_smc.status', '1')
            ->first();
    }

    public function getAntreanSedangPeriksaProperty()
    {
        return Pintu::query()
            ->antreanPerPintu($this->kd_pintu)
            ->where('antripintu_smc.status', '2')
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

            // Gunakan dispatch untuk Livewire v3, atau dispatchBrowserEvent untuk v2
            if (method_exists($this, 'dispatch')) {
                // Livewire v3
                $this->dispatch('play-voice',
                    $antrean->no_reg,
                    $antrean->nm_pasien,
                    $antrean->nm_pintu
                );
            } else {
                // Livewire v2
                $this->dispatchBrowserEvent('play-voice', [
                    'no_reg'    => $antrean->no_reg,
                    'nm_pasien' => $antrean->nm_pasien,
                    'nm_pintu'  => $antrean->nm_pintu,
                ]);
            }
        }
    }

    public function updateStatus(): void
    {
        try {
            $antrean = $this->antreanDipanggilSekarang;

            if ($antrean) {

                tracker_start('mysql_sik');

                try {
                    DB::connection('mysql_sik')->transaction(function () use (&$antrean) {
                        $conn = DB::connection('mysql_sik');

                        $conn->table('antripintu_smc')
                            ->where('kd_pintu', $antrean['kd_pintu'])
                            ->where('status', '2')
                            ->update(['status' => '0']);

                        $conn->table('antripintu_smc')
                            ->where('no_rawat', $antrean['no_rawat'])
                            ->where('kd_pintu', $antrean['kd_pintu'])
                            ->where('status', '1')
                            ->update(['status' => '2']);
                    });
                } catch (Exception $e) {
                    Log::error('Error updating antrean status: '.$e->getMessage());
                }

                tracker_end('mysql_sik');
            }

            $this->isCalling = false;
            $this->antreanDipanggilSekarang = null;

            // Tidak ada return atau redirect di sini

        } catch (Exception $e) {
            Log::error('Error in updateStatus: '.$e->getMessage());
            $this->isCalling = false;
            $this->antreanDipanggilSekarang = null;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.antrean-di-panggil');
    }
}
