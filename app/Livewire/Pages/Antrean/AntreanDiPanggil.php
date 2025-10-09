<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Antrian\AntriPoli;
use App\Models\Antrian\LogAntrean;
use App\Models\Aplikasi\Pintu;
use Illuminate\Database\Query\JoinClause;
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
        $db = DB::connection('mysql_sik')->getDatabaseName();
        $antripoli = DB::raw("{$db}.antripoli antripoli");

        return Pintu::query()
            ->antrianPerPintu($this->kd_pintu)
            ->selectRaw('antripoli.status')
            ->leftJoin($antripoli, fn (JoinClause $join) => $join
                ->on('registrasi.no_rawat', '=', 'antripoli.no_rawat')
                ->on('poliklinik.kd_poli', '=', 'antripoli.kd_poli')
                ->on('dokter.kd_dokter', '=', 'antripoli.kd_dokter')
            )
            ->where('antripoli.status', '1')
            ->first();
    }

    public function getAntreanSedangPeriksaProperty()
    {
        return LogAntrean::query()->with(['dokter', 'pintu', 'poliklinik', 'registrasi', 'registrasi.pasien'])->where('kd_pintu', $this->kd_pintu)->latest()->first();
    }

    public function call(): void
    {
        if ($this->isCalling) {
            return;
        }

        $antrean = $this->getAntreanDiPanggilProperty();

        if ($antrean) {
            $this->isCalling = true;

            try {
                DB::connection('mysql_smc')->transaction(function () use (&$antrean) {
                    tracker_start('mysql_smc');

                        LogAntrean::query()->create([
                            'kd_pintu'  => $this->kd_pintu,
                            'kd_poli'   => $antrean->kd_poli,
                            'kd_dokter' => $antrean->kd_dokter,
                            'no_rawat'  => $antrean->no_rawat,
                        ]);

                    tracker_end('mysql_smc');
                });
            } catch (\Exception $e) {
                Log::error($e);
            }

            $this->antreanDipanggilSekarang = [
                'no_rawat' => $antrean->no_rawat,
                'kd_poli' => $antrean->kd_poli,
                'kd_dokter' => $antrean->kd_dokter,
                'no_reg' => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'nm_pintu' => $antrean->nm_pintu,
            ];
            $this->dispatchBrowserEvent('play-voice', [
                'no_reg'    => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'nm_pintu' => $antrean->nm_pintu,
            ]);
        }
    }

    public function updateStatus()
    {
        $antrean = $this->antreanDipanggilSekarang;

        if ($antrean) {
            try {
                DB::connection('mysql_sik')->transaction(function () use (&$antrean) {
                    tracker_start('mysql_sik');

                        AntriPoli::query()
                            ->where('no_rawat', $antrean['no_rawat'])
                            ->where('kd_poli', $antrean['kd_poli'])
                            ->where('kd_dokter', $antrean['kd_dokter'])
                            ->where('status', '1')
                            ->update(['status' => '0']);

                    tracker_end('mysql_sik');
                });
            } catch (\Exception $e) {
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
