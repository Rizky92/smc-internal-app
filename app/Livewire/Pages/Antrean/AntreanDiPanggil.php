<?php

namespace App\Livewire\Pages\Antrean;

use App\Models\Antrian\AntriPoli;
use App\Models\Aplikasi\Pintu;
use Illuminate\Database\Query\JoinClause;
use Illuminate\View\View;
use Livewire\Component;

class AntreanDiPanggil extends Component
{
    /** @var string */
    public $kd_pintu;

    /** @var mixed */
    protected $listeners = ['updateStatusAfterCall'];

    public function mount(string $kd_pintu): void
    {
       $this->kd_pintu = $kd_pintu;
    }

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
                ->where('antripoli.status', '1')
            )
            ->first();
    }

    public function call(): void
    {
        $antrean = $this->antreanDiPanggil;

        if ($antrean && $antrean->status == '1') {
            $this->dispatchBrowserEvent('play-voice', [
                'no_reg'    => $antrean->no_reg,
                'nm_pasien' => $antrean->nm_pasien,
                'nm_poli'   => $antrean->nm_poli
            ]);
        }
    }

    public function updateStatusAfterCall(): void
    {
        AntriPoli::query()
            ->where('no_rawat', $this->antreanDiPanggil->no_rawat)
            ->where('kd_poli', $this->antreanDiPanggil->kd_poli)
            ->where('status', '1')
            ->update(['status' => '0']);
    }

    public function render(): View
    {
        return view('livewire.pages.antrean.antrean-di-panggil');
    }
}