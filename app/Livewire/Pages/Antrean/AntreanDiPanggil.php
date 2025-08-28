<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Antrian\AntriPoli;
use App\Models\Aplikasi\Pintu;
use Illuminate\Database\Query\JoinClause;
use Illuminate\View\View;
use Livewire\Component;

class AntreanDiPanggil extends Component
{
    public string $kd_pintu;

    public bool $isCalling = false;

    protected $listeners = ['updateStatus'];

    public function getAntreanDiPanggilProperty()
    {
        $db = \DB::connection('mysql_sik')->getDatabaseName();
        $antripoli = \DB::raw("{$db}.antripoli antripoli");

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
        $db = \DB::connection('mysql_sik')->getDatabaseName();
        $antripoli = \DB::raw("{$db}.antripoli antripoli");

        return Pintu::query()
            ->antrianPerPintu($this->kd_pintu)
            ->selectRaw('antripoli.status')
            ->leftJoin($antripoli, fn (JoinClause $join) => $join
                ->on('registrasi.no_rawat', '=', 'antripoli.no_rawat')
                ->on('poliklinik.kd_poli', '=', 'antripoli.kd_poli')
                ->on('dokter.kd_dokter', '=', 'antripoli.kd_dokter')
            )
            ->where('antripoli.status', '0')
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
            $this->dispatchBrowserEvent('play-voice', [
                'no_reg'    => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'nm_pintu' => $antrean->nm_pintu,
            ]);
        }
    }

    public function updateStatus()
    {
        $antrean = $this->getAntreanDiPanggilProperty();

        if ($antrean) {
            AntriPoli::query()
                ->where('no_rawat', $antrean->no_rawat)
                ->where('kd_poli', $antrean->kd_poli)
                ->where('kd_dokter', $antrean->kd_dokter)
                ->where('status', '1')
                ->update(['status' => '0']);
        }

        $this->isCalling = false;
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.antrean-di-panggil');
    }
}
